<?php

namespace App\DomainModel\Sandbox;

use App\Application\UseCase\AuthorizeSandbox\AuthorizeSandboxDTO;

interface ProdAccessClientInterface
{
    public function authorizeTokenForSandbox(string $token): AuthorizeSandboxDTO;
}
