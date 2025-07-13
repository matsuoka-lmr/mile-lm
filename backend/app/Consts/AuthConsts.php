<?php
namespace App\Consts;

class AuthConsts
{
    const COMPANY_TYPE_MAMOL = 0;
    const COMPANY_TYPE_SHOP = 1;
    const COMPANY_TYPE_CUST = 2;
    const ROLE_SYS_ADMIN = 99;
    const ROLE_MAMOL_USER = 1;
    const ROLE_MAMOL_ADMIN = 9;
    const MAMOL_ROLES = [AuthConsts::ROLE_MAMOL_USER, AuthConsts::ROLE_MAMOL_ADMIN];
    const ROLE_SHOP_USER = 11;
    const ROLE_SHOP_ADMIN = 19;
    const SHOP_ROLES = [AuthConsts::ROLE_SHOP_USER, AuthConsts::ROLE_SHOP_ADMIN];
    const ROLE_CUST_USER = 21;
    const ROLE_CUST_ADMIN = 29;
    const CUST_ROLES = [AuthConsts::ROLE_CUST_USER, AuthConsts::ROLE_CUST_ADMIN];
    const MAMOL_SHOP_ROLES = [AuthConsts::ROLE_MAMOL_USER, AuthConsts::ROLE_MAMOL_ADMIN, AuthConsts::ROLE_SHOP_USER, AuthConsts::ROLE_SHOP_ADMIN];
    const ALL_ROLES = [AuthConsts::ROLE_MAMOL_USER, AuthConsts::ROLE_MAMOL_ADMIN, AuthConsts::ROLE_SHOP_USER, AuthConsts::ROLE_SHOP_ADMIN, AuthConsts::ROLE_CUST_USER, AuthConsts::ROLE_CUST_ADMIN];
}
