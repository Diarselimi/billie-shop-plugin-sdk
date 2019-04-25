<?php

namespace App\DomainModel\MerchantUser;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantUserEntity extends AbstractTimestampableEntity
{
    const ROLE_USER = 'ROLE_USER';

    const ROLE_MERCHANT = 'ROLE_MERCHANT';

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
