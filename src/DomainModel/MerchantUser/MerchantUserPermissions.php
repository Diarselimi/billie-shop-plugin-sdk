<?php

namespace App\DomainModel\MerchantUser;

interface MerchantUserPermissions
{
    public const VIEW_ORDERS = 'VIEW_ORDERS';

    public const VIEW_DEBTORS = 'VIEW_DEBTORS';

    public const VIEW_PAYMENTS = 'VIEW_PAYMENTS';

    public const VIEW_USERS = 'VIEW_USERS';

    public const VIEW_ONBOARDING = 'VIEW_ONBOARDING';

    public const VIEW_CREDENTIALS = 'VIEW_CREDENTIALS';

    public const ACCESS_TEST_DATA = 'ACCESS_TEST_DATA';

    public const CONFIRM_ORDER_PAYMENT = 'CONFIRM_ORDER_PAYMENT';

    public const PAUSE_DUNNING = 'PAUSE_DUNNING';

    public const MANAGE_USERS = 'MANAGE_USERS';

    public const CANCEL_ORDERS = 'CANCEL_ORDERS';

    public const CANCEL_INVOICES = 'CANCEL_INVOICES';

    public const MANAGE_ONBOARDING = 'MANAGE_ONBOARDING';

    public const CHANGE_DEBTOR_INFORMATION = 'CHANGE_DEBTOR_INFORMATION';

    public const UPDATE_ORDERS = 'UPDATE_ORDERS';

    public const SHIP_ORDERS = 'SHIP_ORDERS';

    public const CREATE_ORDERS = 'CREATE_ORDERS';

    public const ALL_PERMISSIONS = [
        // read:
        self::VIEW_ORDERS,
        self::VIEW_DEBTORS,
        self::VIEW_PAYMENTS,
        self::VIEW_USERS,
        self::VIEW_ONBOARDING,
        // read (special):
        self::VIEW_CREDENTIALS,
        // access (special)
        self::ACCESS_TEST_DATA,
        // write:
        self::CONFIRM_ORDER_PAYMENT,
        self::PAUSE_DUNNING,
        self::MANAGE_USERS,
        self::CANCEL_ORDERS,
        self::CANCEL_INVOICES,
        self::MANAGE_ONBOARDING,
        self::CHANGE_DEBTOR_INFORMATION,
        self::UPDATE_ORDERS,
        self::SHIP_ORDERS,
        self::CREATE_ORDERS,
    ];

    /**
     * All read permissions suitable for all "read only" users.
     * This does not include sensitive permissions like VIEW_CREDENTIALS.
     */
    public const ALL_READ_PERMISSIONS = [
        self::VIEW_ORDERS,
        self::VIEW_DEBTORS,
        self::VIEW_PAYMENTS,
        self::VIEW_USERS,
        self::VIEW_ONBOARDING,
    ];

    public const ALL_WRITE_PERMISSIONS = [
        self::CONFIRM_ORDER_PAYMENT,
        self::PAUSE_DUNNING,
        self::MANAGE_USERS,
        self::CANCEL_ORDERS,
        self::CANCEL_INVOICES,
        self::MANAGE_ONBOARDING,
        self::CHANGE_DEBTOR_INFORMATION,
        self::UPDATE_ORDERS,
        self::SHIP_ORDERS,
        self::CREATE_ORDERS,
    ];
}
