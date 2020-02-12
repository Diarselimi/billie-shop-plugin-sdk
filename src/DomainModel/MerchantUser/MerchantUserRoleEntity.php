<?php

namespace App\DomainModel\MerchantUser;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantUserRoleEntity extends AbstractTimestampableEntity
{
    private $uuid;

    private $merchantId;

    private $name;

    private $permissions;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): MerchantUserRoleEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantUserRoleEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MerchantUserRoleEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): MerchantUserRoleEntity
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function isTcAcceptanceRequired(): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->getName() === MerchantUserDefaultRoles::ROLE_ADMIN['name'];
    }
}
