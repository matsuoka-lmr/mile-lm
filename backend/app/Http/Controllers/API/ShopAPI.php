<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Models\Company;
use App\Http\Controllers\QueryAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    ];

    protected $roles = [AuthConsts::ROLE_MAMOL_ADMIN, AuthConsts::ROLE_MAMOL_USER, AuthConsts::ROLE_SHOP_USER, AuthConsts::ROLE_SHOP_ADMIN];

    protected function query(Request $request, $params) {
        $query = Company::where('type', AuthConsts::COMPANY_TYPE_SHOP);

        // システム管理者とMAMOL管理者はすべてのショップ情報を見れる
        if ($this->user->role == AuthConsts::ROLE_SYS_ADMIN || $this->user->role == AuthConsts::ROLE_MAMOL_ADMIN) {
            // フィルタリングなし
        } elseif (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            // 店舗ユーザー/管理者は自分の会社のショップのみ
            $query = $query->where('manage_company_id', $this->user->company_id);
        } elseif ($this->user->role == AuthConsts::ROLE_MAMOL_USER) {
            // MAMOLユーザーはすべてのショップ情報を見れる
            // フィルタリングなし
        } else {
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
