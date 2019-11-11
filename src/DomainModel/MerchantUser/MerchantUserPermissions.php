<?php

namespace App\DomainModel\MerchantUser;

interface MerchantUserPermissions
{
    public const VIEW_ORDERS = 'VIEW_ORDERS';

    public const VIEW_DEBTORS = 'VIEW_DEBTORS';

    public const VIEW_PAYMENTS = 'VIEW_PAYMENTS';

    public const VIEW_USERS = 'VIEW_USERS';

    public const CONFIRM_ORDER_PAYMENT = 'CONFIRM_ORDER_PAYMENT';

    public const PAUSE_DUNNING = 'PAUSE_DUNNING';

    public const MANAGE_USERS = 'MANAGE_USERS';

    public const CANCEL_ORDERS = 'CANCEL_ORDERS';

    public const ALL_PERMISSIONS = [
        // read:
        self::VIEW_ORDERS,
        self::VIEW_DEBTORS,
        self::VIEW_PAYMENTS,
        self::VIEW_USERS,
        // write:
        self::CONFIRM_ORDER_PAYMENT,
        self::PAUSE_DUNNING,
        self::MANAGE_USERS,
        self::CANCEL_ORDERS,
    ];

    public const ALL_READ_PERMISSIONS = [
        self::VIEW_ORDERS,
        self::VIEW_DEBTORS,
        self::VIEW_PAYMENTS,
        self::VIEW_USERS,
    ];

    public const ALL_WRITE_PERMISSIONS = [
        self::CONFIRM_ORDER_PAYMENT,
        self::PAUSE_DUNNING,
        self::MANAGE_USERS,
        self::CANCEL_ORDERS,
    ];
}
