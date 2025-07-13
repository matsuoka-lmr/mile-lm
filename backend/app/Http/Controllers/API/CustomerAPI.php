<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Models\Company;
use App\Http\Controllers\QueryAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerAPI extends QueryAPI
{
    protected $filters = [
        'manage_company_id' => '=',
        'name' => 'like',
        'phone' => 'like',
        'address' => 'like',
        'status' => '=',
    ];

    protected $orders = [
        'name' => false
    ];

    protected $validations = [
        'manage_company_id' => 'required',
        'name' => 'required',
        'phone' => 'numeric|nullable',
        'address' => 'string|nullable',
        'status' => 'numeric|nullable',
    ];

    protected $roles = [
        AuthConsts::ROLE_MAMOL_ADMIN,
        AuthConsts::ROLE_MAMOL_USER,
        AuthConsts::ROLE_SHOP_ADMIN,
        AuthConsts::ROLE_SHOP_USER
    ];

    protected function query(Request $request, $params) {
        $query = Company::where('type', AuthConsts::COMPANY_TYPE_CUST);
        // システム管理者、MAMOL管理者、MAMOLユーザーはすべての顧客情報を見れる
        if ($this->user->role == AuthConsts::ROLE_SYS_ADMIN ||
            $this->user->role == AuthConsts::ROLE_MAMOL_ADMIN ||
            $this->user->role == AuthConsts::ROLE_MAMOL_USER) {
            // フィルタリングなし
        } elseif (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            // 店舗ユーザー/管理者は自分の会社の顧客のみ
            $query = $query->where('manage_company_id', $this->user->company_id);
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
        $params['type'] = AuthConsts::COMPANY_TYPE_CUST;
        if (in_array($this->user->role, AuthConsts::SHOP_ROLES)) {
            $params['manage_company_id'] = $this->user->company_id;
        }
        $data->fill($params);
        return $data;
    }
}
