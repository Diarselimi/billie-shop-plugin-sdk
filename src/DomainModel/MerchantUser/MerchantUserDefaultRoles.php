<?php

namespace App\DomainModel\MerchantUser;

interface MerchantUserDefaultRoles
{
    /**
     * Default roles that will be created for new merchants
     */
    public const ROLES = [
        self::ROLE_NONE,
        self::ROLE_ADMIN,
        self::ROLE_BILLIE_ADMIN,
        self::ROLE_VIEW_ONLY,
        self::ROLE_SUPPORT,
    ];

    public const ROLE_NONE = [
        'name' => 'none',
        'permissions' => [],
    ];

    public const ROLE_ADMIN = [
        'name' => 'admin',
        'permissions' => MerchantUserPermissions::ALL_PERMISSIONS,
    ];

    public const ROLE_BILLIE_ADMIN = [
        'name' => 'billie_admin',
        'permissions' => MerchantUserPermissions::ALL_PERMISSIONS,
    ];

    public const ROLE_VIEW_ONLY = [
        'name' => 'view_only',
        'permissions' => MerchantUserPermissions::ALL_READ_PERMISSIONS,
    ];

    public const ROLE_SUPPORT = [
        'name' => 'support',
        'permissions' => [
            // read:
            MerchantUserPermissions::VIEW_ORDERS,
            MerchantUserPermissions::VIEW_DEBTORS,
            MerchantUserPermissions::VIEW_PAYMENTS,
            MerchantUserPermissions::VIEW_USERS,
            // write:
            MerchantUserPermissions::CONFIRM_ORDER_PAYMENT,
            MerchantUserPermissions::PAUSE_DUNNING,
            MerchantUserPermissions::CANCEL_ORDERS,
        ],
    ];
}
