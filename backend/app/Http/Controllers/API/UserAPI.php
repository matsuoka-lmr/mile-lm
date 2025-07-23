<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Models\User;
use App\Models\Company;
use App\Http\Controllers\QueryAPI;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserAPI extends QueryAPI
{
    protected $filters = [
        'role' => '=',
        'email' => 'like',
        'name' => 'like',
        'status' => '=',
    ];

    protected $orders = [
        'id' => false,
        'email' => false,
        'name' => false,
    ];

    protected $validations = [
        'role' => 'numeric|required',
        'name' => 'required',
        'phone' => 'numeric|nullable',
        'password' => 'nullable',
        'status' => 'numeric|nullable',
    ];

    protected function createValidations($request) {
        return array_merge($this->validations, [
            'company_id' => 'required',
            'email' => 'email|required|unique:user',
            'password' => 'required'
        ]);
    }

    protected function updateValidations($request, $id) {
        return array_merge($this->validations, [
            'email' => ['email','required', Rule::unique('user')->ignore($id)]
        ]);
    }

    protected $roles = [AuthConsts::ROLE_MAMOL_ADMIN, AuthConsts::ROLE_MAMOL_USER, AuthConsts::ROLE_SHOP_ADMIN];
    protected $listRoles = [AuthConsts::ROLE_MAMOL_ADMIN, AuthConsts::ROLE_MAMOL_USER, AuthConsts::ROLE_SHOP_ADMIN, AuthConsts::ROLE_SHOP_USER];

    protected function additonalCreateValidate($request, &$params) {
        if ($params['company_id'] != $this->user->company_id) {
            $company = Company::find($params['company_id']);
            if (!$company || $company->manage_company_id != $this->user->company_id) return ['company_id'=>'会社を選択してください'];
        }
        if ($params['role'] != 1 && $params['role'] != 9) return ['role'=>'権限を選択してください'];
        return false;
    }

    protected function query(Request $request, $params) {
        $query = User::with('company');
        $company_id = $request->input('search.company_id', false);
        if ($company_id) {
            $query->where('company_id', $company_id);
        }
        switch($this->user->role) {
            case AuthConsts::ROLE_SYS_ADMIN:
                break;
            case AuthConsts::ROLE_MAMOL_USER:
                $query = $query->where('role', '<>', AuthConsts::ROLE_MAMOL_ADMIN)->where('role', '<>', AuthConsts::ROLE_MAMOL_USER);
            case AuthConsts::ROLE_MAMOL_ADMIN:
                $query = $query->where('role', '<>', AuthConsts::ROLE_SYS_ADMIN);
                break;
            case AuthConsts::ROLE_SHOP_USER:
            case AuthConsts::ROLE_SHOP_ADMIN:
                if ($company_id) {
                    if ($company_id != $this->user->company_id) $this->error('Not Allowed.', 403);
                    break;
                }
                if ($company_id)
                $cid = Company::where('manage_company_id', $this->user->company_id)->where('status',0)->get()->map(function (Company $c) {
                    return $c->id;
                })->toArray();
                if ($this->user->role % 10 == 9) array_push($cid, $this->user->company_id);
                $query = $query->whereIn('company_id', $cid);
                break;
            default:
                $this->error('Not Allowed.', 403);
        }
        return $query;
    }

    protected function newData($request, $params) {
        $data = new User();
        $data->company_id = $params['company_id'];
        $data->role = $params['role'];
        $data->email = $params['email'];
        return $data;
    }

    protected function setData($data, $params, $request) {
        if ($this->user->role !== AuthConsts::ROLE_SYS_ADMIN && $params['role'] == AuthConsts::ROLE_SYS_ADMIN) {
            $this->error('invalid access', 403);
        }

        $params['role'] = $data->company->type * 10 + ($params['role']%10);

        parent::setData($data, $params, $request);
        if (!empty($params['password'])) $data->password = $params['password'];
        if ($this->user->role == AuthConsts::ROLE_SYS_ADMIN) {
            if (array_key_exists('status', $params) && !is_null($params['status'])) $data->status = $params['status'];
        }
        return $data;
    }
}
