<?php

namespace App\DomainModel\MerchantUser;

interface AuthenticationServiceInterface
{
    public function authorizeToken(string $token): ?AuthenticationServiceAuthorizeTokenResponseDTO;

    public function createClient(string $clientName): AuthenticationServiceCreateClientResponseDTO;

    public function createUser(string $email, string $password): AuthenticationServiceUserResponseDTO;

    public function requestUserToken(string $email, string $password): AuthenticationServiceTokenResponseDTO;

    public function revokeToken(string $token): void;

    /**
     * @param  array                                  $uuids
     * @return AuthenticationServiceUserResponseDTO[]
     */
    public function getUsersByUuids(array $uuids): array;

    public function getCredentials(string $clientId): ?GetMerchantCredentialsDTO;
}
