<?php

namespace App\DomainModel\MerchantUser;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantUserEntity extends AbstractTimestampableEntity
{
    private $uuid;

    private $merchantId;

    private $roleId;

    private $firstName;

    private $lastName;

    private $permissions;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param  string $uuid
     * @return $this
     */
    public function setUuid(string $uuid): MerchantUserEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    /**
     * @param  int   $merchantId
     * @return $this
     */
    public function setMerchantId(int $merchantId): MerchantUserEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    /**
     * @param  int   $roleId
     * @return $this
     */
    public function setRoleId(int $roleId): MerchantUserEntity
    {
        $this->roleId = $roleId;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param  string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): MerchantUserEntity
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param  string $lastName
     * @return $this
     */
    public function setLastName(string $lastName): MerchantUserEntity
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param  array $permissions
     * @return $this
     */
    public function setPermissions(array $permissions): MerchantUserEntity
    {
        $this->permissions = $permissions;

        return $this;
    }
}
