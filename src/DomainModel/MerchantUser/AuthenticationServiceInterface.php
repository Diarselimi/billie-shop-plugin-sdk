<?php

namespace App\DomainModel\MerchantUser;

interface AuthenticationServiceInterface
{
    public function authorizeToken(string $token): ? AuthenticationServiceAuthorizeTokenResponseDTO;

    public function createClient(string $clientName): AuthenticationServiceCreateClientResponseDTO;

    public function createUser(string $email, string $password): AuthenticationServiceCreateUserResponseDTO;
}
