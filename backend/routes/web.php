<?php
use \Illuminate\Support\Facades\DB;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    // return $router->app->version();
    return App\Models\User::with('company')->get();
});

$router->get('/vehicles', function () {
    return App\Models\Vehicle::all();
});

$router->get('/histories/{deviceId}', function ($deviceId) {
    return App\Models\History::where('device_id', (int)$deviceId)->get();
});

$router->get('/debug-vehicles', function () {
    return App\Models\Vehicle::whereNotNull('device_id')->get();
});

$router->get('/debug-all-vehicles', function () {
    return App\Models\Vehicle::all();
});