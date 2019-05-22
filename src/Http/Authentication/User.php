<?php

namespace App\Http\Authentication;

use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private const ROLE_DEFAULT = 'ROLE_USER';

    private $merchantId;

    private $username;

    private $password;

    private $roles;

    private $checkoutSession;

    public function __construct(int $merchantId, string $username, string $password, array $roles, ?CheckoutSessionEntity $checkoutSession = null)
    {
        $this->merchantId = $merchantId;
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
        $this->checkoutSession = $checkoutSession;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = self::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function getPassword(): ? string
    {
        return $this->password;
    }

    public function getSalt(): ? string
    {
        return null;
    }

    public function getUsername(): ? string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }

    public function getCheckoutSession(): ?CheckoutSessionEntity
    {
        return $this->checkoutSession;
    }
}
