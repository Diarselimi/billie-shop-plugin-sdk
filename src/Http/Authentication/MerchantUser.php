<?php

namespace App\Http\Authentication;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantUser\MerchantUserEntity;

class MerchantUser extends AbstractUser
{
    private const AUTH_ROLE = 'ROLE_AUTHENTICATED_AS_MERCHANT_USER';

    private $email;

    private $userEntity;

    private $roles;

    public function __construct(MerchantEntity $merchant, ?string $email, MerchantUserEntity $userEntity, array $permissions)
    {
        parent::__construct($merchant);
        $this->email = $email;
        $this->userEntity = $userEntity;

        $this->roles = array_map(function ($name) {
            return 'ROLE_' . $name;
        }, $permissions);

        $this->roles[] = self::AUTH_ROLE;
    }

    public function getUserEntity(): MerchantUserEntity
    {
        return $this->userEntity;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
