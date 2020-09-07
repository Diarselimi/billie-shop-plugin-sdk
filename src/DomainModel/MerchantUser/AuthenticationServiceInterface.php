<?php

namespace App\DomainModel\MerchantUser;

use App\DomainModel\PasswordResetRequest\RequestPasswordResetDTO;

interface AuthenticationServiceInterface
{
    public function authorizeToken(string $token): ?AuthenticationServiceAuthorizeTokenResponseDTO;

    public function createClient(string $clientName): AuthenticationServiceCreateClientResponseDTO;

    public function createUser(string $email, string $password): AuthenticationServiceUserResponseDTO;

    public function requestUserToken(string $email, string $password): AuthenticationServiceTokenResponseDTO;

    public function revokeToken(string $token): void;

    public function getCredentials(string $clientId): ?GetMerchantCredentialsDTO;

    public function requestNewPassword(string $email): RequestPasswordResetDTO;
}
