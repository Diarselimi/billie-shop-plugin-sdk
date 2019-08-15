<?php

namespace App\DomainModel\MerchantUser;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantUserEntity extends AbstractTimestampableEntity
{
    const ROLE_MERCHANT = 'ROLE_MERCHANT';

    const ROLE_CHECKOUT_USER = 'ROLE_CHECKOUT_USER';

    const ROLE_VIEW_ORDERS = 'ROLE_VIEW_ORDERS';

    const ROLE_VIEW_DEBTORS = 'ROLE_VIEW_DEBTORS';

    const ROLE_VIEW_PAYMENTS = 'ROLE_VIEW_PAYMENTS';

    const ROLE_CONFIRM_ORDER_PAYMENT = 'ROLE_CONFIRM_ORDER_PAYMENT';

    const ROLE_PAUSE_DUNNING = 'ROLE_PAUSE_DUNNING';

    const DEFAULT_ROLES = [
        self::ROLE_VIEW_ORDERS,
        self::ROLE_VIEW_DEBTORS,
        self::ROLE_VIEW_PAYMENTS,
        self::ROLE_CONFIRM_ORDER_PAYMENT,
        self::ROLE_PAUSE_DUNNING,
    ];

    private $userId;

    private $merchantId;

    private $roles;

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): MerchantUserEntity
    {
        $this->userId = $userId;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantUserEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): MerchantUserEntity
    {
        $this->roles = $roles;

        return $this;
    }
}
