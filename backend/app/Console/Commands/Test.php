<?php
namespace App\Console\Commands;

use App\Services\Trackimo;
use App\Models\Device;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Development Tests';

    private function login() {

        echo "login!\n";

        $config = config('services.trackimo');
        $http = Http::baseUrl($config['base_url'])->acceptJson();
        $login = $http->setClient(Http::buildClient());
        $resp = $login->post('/api/internal/v2/user/login', [
            'username' => $config['user_name'],
            'password' => $config['password'],
            "rememberMe" => true,
            'remember_me' => true
        ]);
        assert($resp->ok());
        $resp = $login->withoutRedirecting()->get('/api/v3/oauth2/auth', [
            'client_id'=> $config['client_id'],
            'redirect_uri'=> $config['redirect_site'],
            'response_type'=> 'code',
            'scope'=> 'locations,notifications,devices,accounts,settings,geozones'
        ]);
        assert($resp->redirect());
        $redir = parse_url($resp->header('location'));
        assert(is_array($redir) && array_key_exists('query', $redir));
        parse_str($redir['query'], $query);
        $code = $query['code'];
        assert(!empty($code));

        Cache::put('trackimo.auth_code', $code);

        $resp = $login->post('/api/v3/oauth2/token', [
            'client_id'=> $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code
        ]);
        assert($resp->ok());
        $token = $resp->json();
        $expire = Carbon::createFromTimestampUTC($token['expires_in']);
        print_r($expire->format('Y-m-d H:i:s')."\n");
        Cache::put('trackimo.access_token', $token['access_token'], $expire);
        Cache::put('trackimo.refresh_token', $token['refresh_token']);
        return $token['access_token'];
    }

    private function oauthTest() {
        $access_token = Cache::get('trackimo.access_token', function() {$this->login();});

        $config = config('services.trackimo');

        $api = Http::baseUrl($config['base_url'])->withToken($access_token)->acceptJson();
        print_r($api->get('/api/v3/user')->json());
    }

    private function saveFile($uri) {
        $access_token = Cache::get('trackimo.access_token', function() {$this->login();});

        $config = config('services.trackimo');

        $api = Http::baseUrl($config['base_url'])->withToken($access_token)->acceptJson();
        // print_r($api->get('/api/v3/user')->json());

        $file = '/works/innoplus/trackimo-api'.$uri;
        file_put_contents($file, $api->get($uri)->body());

    }

    private function refresh() {
        $config = config('services.trackimo');
        $resp = Http::baseUrl($config['base_url'])->acceptJson()->post('/api/v3/oauth2/token/refresh', [
            'refresh_token' => Cache::get('trackimo.refresh_token'),
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
        ]);
        $token = $resp->json();
        $expire = Carbon::createFromTimestampUTC($token['expires_in']);
        print_r($expire->format('Y-m-d H:i:s')."\n");
        Cache::put('trackimo.access_token', $token['access_token'], $expire);
        Cache::put('trackimo.refresh_token', $token['refresh_token']);
    }

    private function tempTest() {
        // echo Hash::make('Abcd1234');
        print_r(Carbon::createFromTimestampUTC(1740247397)->format('Y-m-d H:i:s')."\n");
        print_r(Carbon::createFromTimestamp(1740247397)->format('Y-m-d H:i:s TZ')."\n");
        print_r(Carbon::now()->format('Y-m-d H:i:s TZ')."\n");
        print_r(strtotime('2024-11-01')."\n");
        print_r(Carbon::createFromTimestamp(strtotime('2024-11-01'))->format("Y-m-d H:i:s TZ\n"));
        // $counter = time();
        // print_r(date("Ymd").sprintf("%07d\n", $counter++%10000000)."\n");
        // Cache::add('test', 0, Carbon::now()->tomorrow());
        // print_r(Cache::increment('test')."\n");
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // $trackimo = app(Trackimo::class);
        // print_r($trackimo->getDevices());
        // print_r($trackimo->getDevicesListDetailsShort());
        $device = Device::where('id', 6021173679)->with('Vehicle')->first();
        // print_r($device->vehicle);
        print_r(json_encode($device));
        // print_r(null === false);
    }

}
