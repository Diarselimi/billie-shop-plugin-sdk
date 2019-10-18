<?php

namespace App\DomainModel\MerchantUser;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantUserEntity extends AbstractTimestampableEntity
{
    private $userId;

    private $merchantId;

    private $roleId;

    private $firstName;

    private $lastName;

    private $permissions;

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

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function setRoleId(int $roleId): MerchantUserEntity
    {
        $this->roleId = $roleId;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): MerchantUserEntity
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): MerchantUserEntity
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): MerchantUserEntity
    {
        $this->permissions = $permissions;

        return $this;
    }
}
