<?php

namespace App\Http\Authentication;

use App\DomainModel\Merchant\MerchantEntity;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractUser implements UserInterface
{
    private $merchant;

    public function __construct(MerchantEntity $merchant)
    {
        $this->merchant = $merchant;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getEmail(): ?string
    {
        return null;
    }

    public function getUsername(): ?string
    {
        return $this->getEmail();
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
}
