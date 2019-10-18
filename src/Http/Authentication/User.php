<?php

namespace App\Http\Authentication;

use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $merchantId;

    private $username;

    private $password;

    private $roles;

    private $checkoutSession;

    private $email;

    public function __construct(
        int $merchantId,
        string $username,
        string $password,
        array $roles,
        ?CheckoutSessionEntity $checkoutSession = null,
        ?string $email = null
    ) {
        $this->merchantId = $merchantId;
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
        $this->checkoutSession = $checkoutSession;
        $this->email = $email;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ? string
    {
        return $this->password;
    }

    public function getSalt(): ? string
    {
        return null;
    }

    public function getEmail(): string
    {
        return $this->email;
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
