<?php

namespace App\Services;

use App\Models\Device;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Trackimo {
    private $api;
    private $user;

    public function __construct($config) {
        $http = Http::baseUrl($config['base_url'])->acceptJson()->timeout(3)->retry(3,100);
        $this->api = $http->withToken($this->getToken($http, $config));
        $res = $this->api->get('/api/v3/user');
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
        $login = $http->setClient(Http::buildClient());
        $resp = $login->post('/api/internal/v2/user/login', [
            'username' => $config['user_name'],
            'password' => $config['password'],
            "rememberMe" => true,
            'remember_me' => true
        ]);
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to login.');

        $resp = $login->withoutRedirecting()->get('/api/v3/oauth2/auth', [
            'client_id'=> $config['client_id'],
            'redirect_uri'=> $config['redirect_site'],
            'response_type'=> 'code',
            'scope'=> 'locations,notifications,devices,accounts,settings,geozones'
        ]);
        $redir = $resp->redirect() ? parse_url($resp->header('location')) : [];
        parse_str($redir['query'], $query);
        $code = $query['code'];
        if (empty($code)) throw new ServiceException('[Trackimo] Failed to get auth code.');

        $resp = $login->post('/api/v3/oauth2/token', [
            'client_id'=> $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code
        ]);
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to get access token.');
        return $this->setTokens($resp->json());
    }

    private function refreshToken($http, $config) {
        $resp = $http->post('/api/v3/oauth2/token/refresh', [
            'refresh_token' => Cache::get('trackimo.refresh_token'),
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);
        return $resp->ok() ? $this->setTokens($resp->json()) : $this->login($http, $config);
    }

    private function getToken($http, $config) {
        return Cache::get('trackimo.access_token', function() use($http, $config) {
            return Cache::has('trackimo.refresh_token') ? $this->refreshToken($http, $config) : $this->login($http, $config);
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

    public function getHistories($device_id, $from, $to) {
        //https://app.trackimo.com/api/v1/accounts/1325071/devices/602117680/location_event/history/details?rsf_enabled=false&from=1739631600&to=1740322799&limit=5000&page=1&types=0,2&error_when_limit_crossed=true
        $resp = $this->api->withUrlParameters([
            'account_id' => $this->user['account_id'],
            'device_id' => $device_id,
        ])->get('/api/v3/accounts/{account_id}/devices/{device_id}/history', [
            'from' => $from,
            'to' => $to
        ]);
        if (!$resp->ok()) throw new ServiceException('[Trackimo] Failed to get device histories.');
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
}
