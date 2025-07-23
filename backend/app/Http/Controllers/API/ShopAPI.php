<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Models\Company;
use App\Http\Controllers\QueryAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopAPI extends QueryAPI
{
    protected $filters = [
        'manage_company_id' => '=',
        'name' => 'like',
        'phone' => 'like',
        'address' => 'like',
        'status' => '=',
    ];

    protected $orders = [
        'create_at' => false
    ];

    protected $validations = [
        'name' => 'required',
        'phone' => 'numeric|nullable',
        'address' => 'string|nullable',
        'status' => 'numeric|nullable',
        'memo' => 'string|nullable',
        'oil_notice_days' => 'numeric|nullable',
        'oil_notice_mileage' => 'numeric|nullable',
        'tire_notice_days' => 'numeric|nullable',
        'tire_notice_mileage' => 'numeric|nullable',
        'battery_notice_days' => 'numeric|nullable',
        'battery_notice_mileage' => 'numeric|nullable',
    ];

    protected $roles = [AuthConsts::ROLE_MAMOL_ADMIN, AuthConsts::ROLE_MAMOL_USER, AuthConsts::ROLE_SHOP_USER, AuthConsts::ROLE_SHOP_ADMIN];

    protected function query(Request $request, $params) {
        \Log::info('ShopAPI query - User Role: ' . $this->user->role);
        $query = Company::where('type', AuthConsts::COMPANY_TYPE_SHOP);

        // システム管理者, MAMOL管理者, MAMOLユーザはすべてのショップ情報を見れる
        if ($this->user->role == AuthConsts::ROLE_SYS_ADMIN || $this->user->role == AuthConsts::ROLE_MAMOL_ADMIN || $this->user->role == AuthConsts::ROLE_MAMOL_USER) {
            \Log::info('ShopAPI query - Entered SYS_ADMIN, MAMOL_ADMIN, or MAMOL_USER block');
            // フィルタリングなし
        } elseif (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            \Log::info('ShopAPI query - Entered SHOP_ROLES block');
            // 店舗ユーザー/管理者は自分の会社のショップのみ
            $query = $query->where('manage_company_id', $this->user->company_id);
        } else {
            \Log::info('ShopAPI query - Entered ELSE block');
            // その他のロールはアクセス不可
            $this->error('Not Allowed.', 403);
        }
        return $query;
    }

    protected function newData($request, $params) {
        return new Company(['status'=>0]);
    }

    protected function setData($data, $params, $request) {
        $params['type'] = AuthConsts::COMPANY_TYPE_SHOP;
        $params['manage_company_id'] = $this->user->company_id;
        $data->fill($params);
        return $data;
    }

}
