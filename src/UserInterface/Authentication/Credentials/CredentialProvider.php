<?php

namespace App\UserInterface\Authentication\Credentials;

interface CredentialProvider
{
    public function getUser(): string;

    public function getPassword(): string;
}
