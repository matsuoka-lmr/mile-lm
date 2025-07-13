<?php

namespace App\Http\Controllers\API;

use App\Consts\AuthConsts;
use App\Models\Company;
use App\Http\Controllers\QueryAPI;
use Illuminate\Http\Request;

class CompanyAPI extends QueryAPI
{
    protected $filters = [
        'type' => '=',
        'name' => 'like',
        'phone' => 'like',
        'address' => 'like',
        'status' => '=',
    ];

    protected $orders = [
        'id' => false,
        'type' => false,
        'name' => false,
    ];

    protected $validations = [
        'name' => 'required',
        'phone' => 'numeric|nullable',
        'address' => 'string|nullable',
        'status' => 'numeric|nullable',
    ];

    protected function createValidations($request) {
        return array_merge($this->validations, [
            'type' => 'numeric|required',
            'email' => 'email|required|unique:user',
            'password' => 'required'
        ]);
    }

    protected $roles = [AuthConsts::ROLE_MAMOL_ADMIN, AuthConsts::ROLE_MAMOL_USER];

    protected function query(Request $request, $params) {
        $query = Company::query();
        if ($this->user->role==AuthConsts::ROLE_MAMOL_USER) {
            $query = $query->where('type', '<>', AuthConsts::COMPANY_TYPE_MAMOL);
        }
        return $query;
    }

    // protected function newData($request, $params) {
    //     $data = new User();
    //     $data->email = $params['email'];
    //     return $data;
    // }

    // protected function saveDeletedData($data) {
    //     $data->status = 9;
    //     return $data->save();
    // }

    // protected function setData($data, $params, $request) {
    //     if ($this->user->role !== AuthConsts::ROLE_SYS_ADMIN && $params['role'] == AuthConsts::ROLE_SYS_ADMIN) {
    //         $this->error('invalid access', 403);
    //     }
    //     parent::setData($data, $params, $request);
    //     if (!empty($params['password'])) $data->password = $params['password'];
    //     if ($this->user->role == AuthConsts::ROLE_SYS_ADMIN) {
    //         if (array_key_exists('status', $params) && !is_null($params['status'])) $data->status = $params['status'];
    //     }
    //     if ($data->status != 9) {
    //         $today = date('Y-m-d');
    //         $data->status =
    //             $today < $params['start_date'] || (
    //                 !empty($params['end_date']) && $today > $params['end_date']
    //             ) ?
    //             1 : 0;
    //     }
    //     return $data;
    // }
}
