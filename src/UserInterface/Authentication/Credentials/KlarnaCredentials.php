<?php

declare(strict_types=1);

namespace App\UserInterface\Authentication\Credentials;

class KlarnaCredentials implements CredentialProvider
{
    private string $klarnaUser;

    private string $klarnaPassword;

    public function __construct(string $klarnaUser, string $klarnaPassword)
    {
        $this->klarnaUser = $klarnaUser;
        $this->klarnaPassword = $klarnaPassword;
    }

    public function getUser(): string
    {
        return $this->klarnaUser;
    }

    public function getPassword(): string
    {
        return $this->klarnaPassword;
    }
}
