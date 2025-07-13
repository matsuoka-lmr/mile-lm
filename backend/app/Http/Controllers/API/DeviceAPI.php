<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Models\Device;
use App\Services\ServiceException;
use App\Services\Trackimo;
use App\Http\Controllers\QueryAPI;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeviceAPI extends QueryAPI
{
    private function updateDetails() {
        try {
            $devices = [];
            foreach(Device::all() as $device) {
                $device->status = "deleted";
                $devices["$device->id"] = $device;
            }
            $now = Carbon::now();
            $trackimo = app(Trackimo::class);
            foreach($trackimo->getDevices() as $remote) {
                $local = array_key_exists($remote['device_id'], $devices) ? $devices[$remote['device_id']] : new Device;
                $local->id = $remote['device_id'];
                $local['name'] = $remote['device_name'];
                $local->battery = $remote['battery'];
                $local->lat = $remote['lat'];
                $local->lng = $remote['lng'];
                $local->last_loc_at = $now->subSeconds($remote['age']);
                $local->status = $remote['status'];
                $local->save();
            }
            foreach($devices as $device) {
                if ($device->status == "deleted") {
                    if (empty($device->company_id)) $device->delete();
                    else $device->save();
                }
            }
        } catch (ServiceException $e) {}
    }

    protected $filters = [
        'id' => 'like',
        'name' => 'like',
        'status' => '=',
    ];

    protected $orders = [
        'id' => false,
        'company_id' => false,
        'name' => false,
    ];

    protected $validations = [
        'id' => 'required',
        'name' => 'required',
        'measure_interval' => 'numeric',
        'company_id' => 'string'
    ];

    protected function query(Request $request, $params) {
        $this->updateDetails();
        $query = Device::query();
        $unusedSearch = $request->input('search.unused', false);
        if ($unusedSearch) {
            $query = $query->doesntHave('vehicle');
            if (is_string($unusedSearch)) $query = $query->orWhere('id', $unusedSearch);
        }
        if (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            $query = $query->where('company_id', $this->user->company_id);
        } else {
            $this->filters['company_id'] = function ($query, $val) {
                if ($val == 'null') $query->whereNull('company_id');
                else $query->where('company_id', '=' , $val);
            };
        }

        return $query;
    }

    protected function getByID(Request $request, $id, $params=[]) {
        $data = Device::where('id', (int)$id)->firstOrFail();
        if (empty($data)) $this->error('Not Found.', 404);
        if ($data->status != 'deleted') {
            try {
                $trackimo = app(Trackimo::class);
                $detail = $trackimo->getDeviceDetail($data->id);
                $data->settings_id = $detail['settings']['id'];
                if (!empty($detail['settings']['id'])) {
                    $data->settings_id = $detail['settings']['id'];
                    if (!empty($detail['settings']['preferences']['tracking_mode'])) {
                        $data->measure_interval = $detail['settings']['preferences']['tracking_mode']['sample_rate'];
                        if ($detail['settings']['preferences']['tracking_mode']['tracking_measurment'] == 'minutes') $data->measure_interval *= 60;
                    }
                }
                if (!empty($detail['features']['minimal_tracking_interval_for_second'])) {
                    $data->minimal_interval = $detail['features']['minimal_tracking_interval_for_second'];
                }
                if (!empty($detail['location'])) {
                    $data->lat = $detail['location']['lat'];
                    $data->lng = $detail['location']['lng'];
                    if (!empty($detail['location']['updated'])) $data->last_loc_at = $detail['location']['updated'] / 1000;
                }
                $data->save(['timestamps' => false]);
            } catch (ServiceException $e) {}
        }
        return $data;
    }

    protected function newData($request, $params) {
        $this->error('Not Implemented.', 500);
    }

    protected function saveCreatedData($data, $params) {
        $this->error('Not Implemented.', 500);
    }

    protected function setData($data, $params, $request) {
        if ($params['company_id'] == 'null') {
            unset($params['company_id']);
            $data->company_id = null;
        }
        $data->fill($params);
        return $data;
    }

    protected function saveUpdatedData($data, $params) {
        try {
            $trackimo = app(Trackimo::class);
            $trackimo->setDeviceNameAndMeasureInterval($data->id, $data->settings_id, $data->name, $data->measure_interval);
            return $data->save();
        } catch (ServiceException $e) {
            return false;
        }
    }

    public function unused(Request $request) {
        $this->auth($request, AuthConsts::MAMOL_SHOP_ROLES);
        $company_id = in_array($this->user->role, AuthConsts::SHOP_ROLES) ? $this->user->company_id : $request->input('company_id');
        if (empty($company_id)) $this->error('no company_id');
        $query = Device::query()->where('company_id', $company_id)->doesntHave('vehicle');
        // $device_id = $request->input('device_id');
        // if (!empty($device_id)) $query = $query->orWhere('id', $device_id);
        return new JsonResponse($query->select('id')->get());
    }


}
