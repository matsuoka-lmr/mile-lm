<?php

namespace Database\Seeders;

use App\Consts\AuthConsts;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::firstOrCreate([
            'type' => AuthConsts::COMPANY_TYPE_MAMOL,
            'status' => 0
        ],[
            'type' => AuthConsts::COMPANY_TYPE_MAMOL,
            'name' => '株式会社マモエル'
        ]);
        User::firstOrCreate([
            'company_id' => $company->id,
            'role' => AuthConsts::ROLE_SYS_ADMIN,
            'status' => 0
        ],[
            'company_id' => $company->id,
            'name' => 'システム管理者',
            'email' => 'admin@demo-work.com',
            'role' => AuthConsts::ROLE_SYS_ADMIN,
            'password' => 'Abcd1234',
            'status' => 0
        ]);

        User::firstOrCreate([
            'company_id' => $company->id,
            'role' => AuthConsts::ROLE_MAMOL_ADMIN,
            'status' => 0
        ],[
            'company_id' => $company->id,
            'name' => 'マモエル管理者',
            'email' => 'admin@mamo-l.jp',
            'role' => AuthConsts::ROLE_MAMOL_ADMIN,
            'password' => 'Abcd1234',
            'status' => 0
        ]);
    }
}
