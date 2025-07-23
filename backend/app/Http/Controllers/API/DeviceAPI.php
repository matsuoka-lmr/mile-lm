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
    private function updateDetails()
    {
        try {
            $trackimo = app(Trackimo::class);
            $remote_devices = $trackimo->getAccountDescendantsAndDevices($trackimo->user['account_id']);

            if (empty($remote_devices)) {
                return;
            }

            $now = Carbon::now();
            $device_ids = []; // To keep track of valid device IDs from remote

            foreach ($remote_devices as $remote) {
                $deviceId = (int)$remote['device_id'];

                if (empty($remote['device_id']) || $deviceId <= 0) {
                    Log::warning('[DeviceAPI] Skipping device with invalid ID.', ['remote_device' => $remote, 'processed_id' => $deviceId]);
                    continue;
                }

                // Trackimo APIからデバイス詳細情報を取得
                $detail = $trackimo->getDeviceDetail($deviceId);
                Log::debug('[DeviceAPI] Full detail from Trackimo API (updateDetails):', ['detail' => $detail]);

                $device_ids[] = $deviceId;

                $device = Device::where('id', $deviceId)->first();

                if (!$device) {
                    $device = new Device();
                    $device->id = $deviceId;
                }

                $device->name = $detail['info']['nick_name'] ?? null;
                $device->imei = $detail['imei'] ?? null;
                $device->battery = $detail['location']['battery'] ?? null;
                Log::debug('[DeviceAPI] Battery from remote (updateDetails): ' . ($detail['location']['battery'] ?? 'N/A'));
                $device->lat = $detail['location']['lat'] ?? null;
                $device->lng = $detail['location']['lng'] ?? null;
                $device->last_loc_at = isset($detail['location']['updated']) ? Carbon::createFromTimestampMs($detail['location']['updated'])->toDateTimeString() : null;
                $device->status = $detail['location']['comm_stat'] ?? null;
                Log::debug('[DeviceAPI] Status from comm_stat (updateDetails): ' . ($device->status ?? 'N/A'));

                if (!empty($detail['settings']['id'])) {
                    $device->settings_id = $detail['settings']['id'];
                    if (!empty($detail['settings']['preferences']['tracking_mode'])) {
                        $device->measure_interval = $detail['settings']['preferences']['tracking_mode']['sample_rate'];
                        if ($detail['settings']['preferences']['tracking_mode']['tracking_measurment'] == 'minutes') {
                            $device->measure_interval *= 60;
                        }
                    }
                }
                if (!empty($detail['features']['minimal_tracking_interval_for_second'])) {
                    $device->minimal_interval = $detail['features']['minimal_tracking_interval_for_second'];
                }

                $device->save();
            }

            // Mark devices that are no longer in the remote list as 'deleted'
            Device::whereNotIn('id', $device_ids)->update(['status' => 'deleted']);

        } catch (ServiceException $e) {
            Log::error('[DeviceAPI] ServiceException in updateDetails: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('[DeviceAPI] Exception in updateDetails: ' . $e->getMessage(), ['exception' => $e]);
        }
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
        Log::debug('[DeviceAPI] query method: Initial device count after updateDetails: ' . $query->count());
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
        if (!is_numeric($id)) {
            $this->error('Invalid Device ID Format.', 400);
        }

        try {
            $trackimo = app(Trackimo::class);
            $detail = $trackimo->getDeviceDetail($id);

            $device = Device::where('id', (int)$id)->first();
            if (!$device) {
                $device = new Device();
                $device->id = (int)$id;
            }

            $device->name = $detail['info']['nick_name'] ?? null;
            $device->imei = $detail['imei'] ?? null;
            $device->battery = $detail['location']['battery'] ?? null;
            Log::debug('[DeviceAPI] Battery from detail (getByID): ' . ($detail['location']['battery'] ?? 'N/A'));
            $device->lat = $detail['location']['lat'] ?? null;
            $device->lng = $detail['location']['lng'] ?? null;
            $device->last_loc_at = isset($detail['location']['updated']) ? Carbon::createFromTimestampMs($detail['location']['updated'])->toDateTimeString() : null;
            $device->status = $detail['location']['comm_stat'] ?? null;
            Log::debug('[DeviceAPI] Status before save (getByID): ' . ($device->status ?? 'N/A'));

            if (!empty($detail['settings']['id'])) {
                $device->settings_id = $detail['settings']['id'];
                if (!empty($detail['settings']['preferences']['tracking_mode'])) {
                    $device->measure_interval = $detail['settings']['preferences']['tracking_mode']['sample_rate'];
                    if ($detail['settings']['preferences']['tracking_mode']['tracking_measurment'] == 'minutes') {
                        $device->measure_interval *= 60;
                    }
                }
            }
            if (!empty($detail['features']['minimal_tracking_interval_for_second'])) {
                $device->minimal_interval = $detail['features']['minimal_tracking_interval_for_second'];
            }

            $device->save();

            return $device;
        } catch (ServiceException $e) {
            Log::error('[DeviceAPI] Trackimo service error in getByID.', ['device_id' => $id, 'error' => $e->getMessage()]);
            $this->error('Device not found via Trackimo API.', 404);
        } catch (\Exception $e) {
            Log::error('[DeviceAPI] Database or unexpected error in getByID.', ['device_id' => $id, 'exception' => $e->getMessage()]);
            if (str_contains($e->getMessage(), 'E11000 duplicate key error')) {
                $this->error('Database unique constraint error. This may be temporary, please try again.', 500);
            }
            $this->error('An internal server error occurred while fetching device details.', 500);
        }
    }

    public function unused(Request $request) {
        $this->auth($request, AuthConsts::MAMOL_SHOP_ROLES);
        $query = Device::query()->doesntHave('vehicle');

        if (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            $query = $query->where('company_id', $this->user->company_id);
        } else {
            $companyId = $request->input('company_id');
            if ($companyId) {
                $query = $query->where('company_id', $companyId);
            }
        }

        $result = $query->get();
        \Illuminate\Support\Facades\Log::debug('[DeviceAPI] unused method response:', ['data' => $result]);
        return new JsonResponse($result);
        Log::debug('[DeviceAPI] unused method response:', ['data' => $query->get()]);
    }
}
