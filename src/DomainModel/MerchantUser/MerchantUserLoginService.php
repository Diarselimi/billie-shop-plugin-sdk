<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserLoginService
{
    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function login(string $email, string $password): MerchantUserLoginDTO
    {
        try {
            $tokenInfo = $this->authenticationService->requestUserToken($email, $password);
        } catch (AuthenticationServiceRequestException $exception) {
            throw new MerchantUserLoginException();
        }

        $tokenMetadata = $this->authenticationService->authorizeToken(
            $tokenInfo->getTokenType() . ' ' . $tokenInfo->getAccessToken()
        );

        if (!$tokenMetadata) {
            throw new MerchantUserLoginException("Invalid token metadata");
        }

        return new MerchantUserLoginDTO(
            $tokenMetadata->getUserId(),
            $tokenMetadata->getEmail(),
            $tokenInfo->getAccessToken()
        );
    }
}
