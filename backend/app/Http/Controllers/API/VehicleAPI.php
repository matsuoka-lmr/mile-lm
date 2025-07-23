<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Models\Vehicle;
use App\Models\Company;
use App\Http\Controllers\QueryAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VehicleAPI extends QueryAPI
{
    protected $filters = [
        'company_id' => '=',
        'model' => 'like',
        'number' => 'like',
        'status' => '=',
    ];

    protected $orders = [
        'model' => false,
        'number' => false,
    ];

    protected $validations = [
        'model' => 'required',
        'number' => 'required',
        'device_id' => 'nullable',
        'user_id' => 'nullable',
        'status' => 'numeric|nullable',
        'inspection_date' => 'date|nullable',
        'emails' => 'nullable',
        'oil_date' => 'date|nullable',
        'oil_mileage' => 'numeric|nullable',
        'oil_notice_days' => 'numeric|nullable',
        'oil_notice_mileage' => 'numeric|nullable',
        'tire_date' => 'date|nullable',
        'tire_mileage' => 'numeric|nullable',
        'tire_notice_days' => 'numeric|nullable',
        'tire_notice_mileage' => 'numeric|nullable',
        'battery_date' => 'date|nullable',
        'battery_mileage' => 'numeric|nullable',
        'battery_notice_days' => 'numeric|nullable',
        'battery_notice_mileage' => 'numeric|nullable',
    ];

    protected function createValidations($request) {
        return array_merge($this->validations, [
            'company_id' => 'required',
        ]);
    }

    protected function additonalCreateValidate($request, &$params) {
        if (in_array($this->user->role, AuthConsts::CUST_ROLES)) {
            $params['company_id'] = $this->user->company_id;
        } else if (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            $company = Company::find($params['company_id']);
            if (!$company || $company->manage_company_id != $this->user->company_id) return ['company_id'=>'顧客を選択してください'];
        } else {
            $company = Company::find($params['company_id']);
            if (!$company || $company->type != AuthConsts::COMPANY_TYPE_CUST) return ['company_id'=>'顧客を選択してください'];
        }
        return false;
    }


    protected function query(Request $request, $params) {
        $query = Vehicle::query();
        if (in_array($this->user->role, AuthConsts::CUST_ROLES)) {
            $query = $query->where('company_id', $this->user->company_id);
        } else if (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            $cid = Company::where('manage_company_id', $this->user->company_id)->get()->map(function (Company $c) {
                return $c->id;
            })->toArray();
            $query = $query->whereIn('company_id', $cid);
        }

        return $query;
    }

    protected function newData($request, $params) {
        return new Vehicle();
    }

    protected function setData($data, $params, $request) {
        if (!empty($params['device_id']) && $data->device_id != $params['device_id']) {
            $data->attach_at = Carbon::now();
        }
        if (empty($params['user_id'])) {
            $data->user_id = null;
        }
        $data->fill($params);
        return $data;
    }

    public function reset(Request $request, $id) {
        $this->auth($request, AuthConsts::MAMOL_SHOP_ROLES);
        $vehicle = Vehicle::find($id);
        if (in_array($this->user->role, AuthConsts::SHOP_ROLES) && $this->user->company_id != $vehicle->company->manage_company_id) {
            $this->error('Invalid');
        }
        $now = Carbon::now();
        switch ($request->input('type')) {
            case 'oil':
                $vehicle->oil_date = $now;
                $vehicle->oil_mileage = 0;
                $vehicle->save();
                break;

            case 'tire':
                $vehicle->tire_date = $now;
                $vehicle->tire_mileage = 0;
                $vehicle->save();
                break;

            case 'battery':
                $vehicle->battery_date = $now;
                $vehicle->battery_mileage = 0;
                $vehicle->save();
                break;

            default:
                $this->error('Invalid reset type');
                break;
        }
        return new JsonResponse(['success' => $now->format('Y-m-d')]);
    }

    public function showCreateForm(Request $request) {
        // Simply return a success response for the form display
        return new JsonResponse(['success' => true]);
    }
}
