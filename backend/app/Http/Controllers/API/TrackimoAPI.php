<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Services\Trackimo;
use App\Http\Controllers\BaseAPI;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TrackimoAPI extends BaseAPI
{
    public function address(Request $request, Trackimo $trackimo) {
        $this->auth($request, AuthConsts::ALL_ROLES);
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $key = "addr_{$lat}_{$lng}";
        return new JsonResponse(["address" => Cache::rememberForever($key, function () use($lat, $lng, $trackimo) {
            return $trackimo->getAddress($lat, $lng);
        })]);
    }
}
