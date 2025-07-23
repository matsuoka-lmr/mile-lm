<?php

namespace App\Services;

use App\Models\Device;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Trackimo {
    private $api;
    public $user;

    public function __construct($config) {
        Log::info('[Trackimo] Trackimo service constructor called.');
        $http = Http::baseUrl($config['base_url'])->acceptJson()->timeout(3)->retry(3,100);
        $this->api = $http->withToken($this->getToken($http, $config));
        try {
            $res = $this->api->get('/api/v3/user');
        } catch (\Exception $e) {
            Log::error('[Trackimo] Exception during initial /api/v3/user call:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'response_body' => method_exists($e, 'response') ? $e->response()->body() : 'N/A' // Attempt to get response body if available
            ]);
            // Re-attempt after clearing cache, as per original logic
            Cache::forget('trackimo.access_token');
            Cache::forget('trackimo.refresh_token');
            $this->api = $http->withToken($this->getToken($http, $config));
            $res = $this->api->get('/api/v3/user');
        }

        if ($res->failed()) {
            Cache::forget('trackimo.access_token');
            Cache::forget('trackimo.refresh_token');
            $this->api = $http->withToken($this->getToken($http, $config));
            $res = $this->api->get('/api/v3/user');
        }
        $this->user = $res->json();
    }

    private function setTokens($tokens) {
        Cache::forever('trackimo.refresh_token', $tokens['refresh_token']);
        Cache::put('trackimo.access_token', $tokens['access_token'], Carbon::createFromTimestampUTC($tokens['expires_in']));
        return $tokens['access_token'];
    }

    private function login($http, $config) {
        Log::info('[Trackimo] login called.');
        $login = $http->setClient(Http::buildClient());
        Log::debug('[Trackimo] Attempting login with:', ['username' => $config['user_name'], 'password' => '********']);
        $resp = $login->post('/api/internal/v2/user/login', [
            'username' => $config['user_name'],
            'password' => $config['password'],
            "rememberMe" => true,
            'remember_me' => true
        ]);
        if (!$resp->ok()) {
            Log::error('[Trackimo] Login failed:', ['status' => $resp->status(), 'body' => $resp->body(), 'headers' => $resp->headers()]);
            throw new ServiceException('[Trackimo] Failed to login.');
        }
        Log::debug('[Trackimo] Login successful, response:', ['status' => $resp->status(), 'body' => $resp->body()]);

        Log::debug('[Trackimo] Attempting to get auth code.');
        $resp = $login->withoutRedirecting()->get('/api/v3/oauth2/auth', [
            'client_id'=> $config['client_id'],
            'redirect_uri'=> $config['redirect_site'],
            'response_type'=> 'code',
            'scope'=> 'locations,notifications,devices,accounts,settings,geozones'
        ]);
        $locationHeader = $resp->header('location');
        if ($resp->status() !== 302 || empty($locationHeader)) {
            Log::error('[Trackimo] Failed to get auth code: Unexpected response or missing Location header.', ['status' => $resp->status(), 'body' => $resp->body(), 'headers' => $resp->headers()]);
            throw new ServiceException('[Trackimo] Failed to get auth code.');
        }
        Log::debug('[Trackimo] Auth code response:', ['status' => $resp->status(), 'body' => $resp->body(), 'headers' => $resp->headers()]);
        $locationHeader = $resp->header('location');
        Log::debug('[Trackimo] Raw Location header value:', ['location_header' => $locationHeader]);
        $query = [];
        if (!empty($locationHeader)) {
            $parsedUrl = parse_url($locationHeader);
            Log::debug('[Trackimo] Result of parse_url (raw):', ['parsed_url_raw_result' => $parsedUrl]);
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $query);
                Log::debug('[Trackimo] Parsed URL query string:', ['parsed_query_string' => $parsedUrl['query']]);
                Log::debug('[Trackimo] Query array after parse_str:', ['query_array_after_parse_str' => $query]);
            }
        }
        $code = $query['code'] ?? null;
        Log::debug('[Trackimo] Final $code value before empty check:', ['final_code' => $code, 'is_empty' => empty($code)]);
        if (empty($code)) {
            Log::error('[Trackimo] Auth code is empty.', ['redirect_url' => $locationHeader, 'query_array' => $query]);
            throw new ServiceException('[Trackimo] Failed to get auth code.');
        }
        Log::debug('[Trackimo] Auth code obtained:', ['code' => $code]);

        Log::debug('[Trackimo] Attempting to get access token.');
        Log::debug('[Trackimo] Attempting to get access token with parameters:', ['client_id' => $config['client_id'], 'client_secret' => '********', 'code' => $code]);
        try {
            $resp = $login->post('/api/v3/oauth2/token', [
                'client_id'=> $config['client_id'],
                'client_secret' => $config['client_secret'],
                'code' => $code
            ]);
            if (!$resp->ok()) {
                Log::error('[Trackimo] Failed to get access token:', ['status' => $resp->status(), 'body' => $resp->body(), 'headers' => $resp->headers()]);
                throw new ServiceException('[Trackimo] Failed to get access token.');
            }
            Log::debug('[Trackimo] Access token response:', ['status' => $resp->status(), 'body' => $resp->body()]);
            return $this->setTokens($resp->json());
        } catch (\Exception $e) {
            Log::error('[Trackimo] Exception during access token retrieval HTTP request:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'response_body' => method_exists($e, 'response') ? $e->response()->body() : 'N/A'
            ]);
            throw $e;
        }
    }

    private function refreshToken($http, $config) {
        Log::info('[Trackimo] refreshToken called.');
        Log::debug('[Trackimo] Attempting to refresh token with parameters:', ['refresh_token' => '********', 'client_id' => $config['client_id'], 'client_secret' => '********']);
        try {
            $resp = $http->post('/api/v3/oauth2/token/refresh', [
                'refresh_token' => Cache::get('trackimo.refresh_token'),
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
            ]);

            if ($resp->ok()) {
                return $this->setTokens($resp->json());
            } else {
                // If refresh token failed, log and attempt full login
                Log::error('[Trackimo] Refresh token failed with status ' . $resp->status() . ':', [
                    'body' => $resp->body(),
                    'headers' => $resp->headers()
                ]);
                return $this->login($http, $config); // Attempt full login as fallback
            }
        } catch (\Exception $e) {
            // Catch any exception during the HTTP request (e.g., connection error, or Laravel's RequestException)
            Log::error('[Trackimo] Exception during refreshToken HTTP request. Attempting full login as fallback:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'response_body' => method_exists($e, 'response') ? $e->response()->body() : 'N/A'
            ]);
            return $this->login($http, $config); // Attempt full login as fallback
        }
    }

    private function getToken($http, $config) {
        Log::info('[Trackimo] getToken called.');
        return Cache::get('trackimo.access_token', function() use($http, $config) {
            Log::info('[Trackimo] Access token not in cache. Attempting to refresh or login.');
            if (Cache::has('trackimo.refresh_token')) {
                Log::info('[Trackimo] refreshToken called.');
                return $this->refreshToken($http, $config);
            } else {
                Log::info('[Trackimo] login called.');
                return $this->login($http, $config);
            }
        });
    }

    public function getDevices() {
        $api = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id']
        ]);
        $devices = [];
        for ($page = 1, $size=100, $count=$size; count($devices) < $count; $page++) {
            $resp = $api->get('/api/v3/accounts/{account_id}/devices/list/details/short', ['page' => $page, 'size' => $size]);
            if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to list devices details.');
            $list = $resp->json();
            if ($count != $list['count']) $count = $list['count'];
            foreach($list['data'] as $device) array_push($devices, $device);
        }
        Log::debug('[Trackimo] getDevices returned ' . count($devices) . ' devices.');
        return $devices;
    }

    public function getDevicesListDetailsShort() {
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id']
        ])->get('/api/v3/accounts/{account_id}/devices/list/details/short');
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to list devices details.');
        return $resp->json();
    }

    public function getDeviceDetail($device_id) {
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id'],
            'device_id' => $device_id,
        ])->get('/api/v3/accounts/{account_id}/devices/{device_id}/details/full');
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to get device detail.');
        return $resp->json();
    }

    public function getAddress($lat, $lng) {
        $resp = $this->api->get('/geolocation/api/v1/geolocation/getaddress', [
            'latitude' => $lat,
            'longitude' => $lng,
            'language' => 'ja'
        ]);
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to get address.');
        return $resp->body();
    }

    public function getHistories($device_id, $from, $to, $limit = 1000, $page = 1) {
        Log::debug("[Trackimo] getHistories API call parameters: " . json_encode(['device_id' => $device_id, 'from' => $from, 'to' => $to, 'limit' => $limit, 'page' => $page]));
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id'],
            'device_id' => $device_id,
        ])->get('/api/v3/accounts/{account_id}/devices/{device_id}/history', [
            'from' => $from,
            'to' => $to,
            'limit' => $limit,
            'page' => $page
        ]);
        Log::debug("[Trackimo] getHistories API response status: " . $resp->status());
        Log::debug("[Trackimo] getHistories API response headers: " . json_encode($resp->headers()));
        Log::debug("[Trackimo] getHistories API response body: " . $resp->body());
        if (!$resp->ok()) {
            Log::error('[Trackimo] Failed to get device histories. Status: ' . $resp->status() . ', Body: ' . $resp->body());
            throw new ServiceException('[Trackimo] Failed to get device histories.');
        }
        return $resp->json();
    }

    public function getLocEvtHistDetails($device_id, $from, $to) {
        //https://app.trackimo.com/api/v1/accounts/1325071/devices/602117680/location_event/history/details?rsf_enabled=false&from=1739631600&to=1740322799&limit=5000&page=1&types=0,2&error_when_limit_crossed=true
        // for ($page=1, $limit=5000;
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id'],
            'device_id' => $device_id,
        ])->get('/api/v1/accounts/{account_id}/devices/{device_id}/location_event/history/details', [
            'rsf_enabled' => 'false',
            'from' => $from,
            'to' => $to,
            'limit' => 5000,
            'page' => 1,
            'error_when_limit_crossed' => true
        ]);
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to get device histories.');
        return $resp->json();
    }

    public function setDeviceNameAndMeasureInterval($device_id, $settings_id, $device_name, $sample_rate) {
        $tracking_measurment = 'seconds';
        if ($sample_rate % 60 == 0) {
            $sample_rate /= 60;
            $tracking_measurment = 'minutes';
        }
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id'],
            'device_id' => $device_id,
        ])->put('/api/v3/accounts/{account_id}/devices/{device_id}/settings', [
            'id' => $settings_id,
            'device_id' => $device_id,
            'device_name' => $device_name,
            'preferences' => [
                'tracking_mode' => [
                    'sample_rate' => $sample_rate,
                    'tracking_measurment' => $tracking_measurment,
                ]
            ]
        ]);
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to set device measure interval.');
    }

    public function getDeviceUsers($device_id) {
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id'],
            'device_id' => $device_id,
        ])->get('/api/v3/accounts/{account_id}/devices/{device_id}/user/list');
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to get device users.');
        return $resp->json();
    }

    public function getDeviceAccounts($device_id) {
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id'],
            'device_id' => $device_id,
        ])->get('/api/v3/accounts/{account_id}/devices/{device_id}/account/list');
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to get device accounts.');
        return $resp->json();
    }

    public function getAccountDescendantsAndDevices($account_id) {
        $resp = $this->api->withUrlParameters([
            'account_id' => (int)$account_id,
        ])->get('/api/v4/accounts/{account_id}/descendants');

        // Log the full response for debugging purposes
        Log::debug('[Trackimo] Full response from /api/v4/accounts/{account_id}/descendants: ' . $resp->body());

        if (!$resp->ok()) {
            Log::error('[Trackimo] Failed to get account descendants and devices.', [
                'status' => $resp->status(),
                'body' => $resp->body()
            ]);
            throw new ServiceException('[Trackimo] Failed to get account descendants and devices.');
        }

        $data = $resp->json();
        
        // Helper function to recursively extract devices
        $extractor = function($account) use (&$extractor) {
            $all_devices = $account['devices'] ?? [];
            if (!empty($account['descendants'])) {
                foreach ($account['descendants'] as $descendant) {
                    $all_devices = array_merge($all_devices, $extractor($descendant));
                }
            }
            return $all_devices;
        };

        return $extractor($data);
    }
}