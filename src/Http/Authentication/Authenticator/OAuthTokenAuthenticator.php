<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\Http\Authentication\User;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthTokenAuthenticator extends AbstractAuthenticator
{
    private $authenticationService;

    private $merchantRepository;

    private $merchantUserRepository;

    private $merchantUserPermissionsService;

    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserPermissionsService $merchantUserPermissionsService
    ) {
        $this->authenticationService = $authenticationService;
        $this->merchantRepository = $merchantRepository;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserPermissionsService = $merchantUserPermissionsService;
    }

    public function supports(Request $request)
    {
        if ($this->wasAlreadyAuthenticated($request)) {
            return false;
        }

        return $request->headers->has(HttpConstantsInterface::REQUEST_HEADER_AUTHORIZATION);
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_AUTHORIZATION);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $tokenMetadata = $this->authenticationService->authorizeToken($credentials);

        if (!$tokenMetadata) {
            throw new AuthenticationException();
        }

        if ($tokenMetadata->getUserId()) {
            return $this->authenticateAsMerchantUser($tokenMetadata->getUserId(), $credentials, $tokenMetadata->getEmail());
        }

        if ($tokenMetadata->getClientId()) {
            return $this->authenticateAsMerchantClient($tokenMetadata->getClientId());
        }

        throw new AuthenticationException();
    }

    private function authenticateAsMerchantClient(string $oauthClientId): UserInterface
    {
        $merchant = $this->merchantRepository->getOneByOauthClientId($oauthClientId);

        if (!$merchant) {
            throw new AuthenticationException();
        }

        return new User(
            $merchant->getId(),
            $merchant->getName(),
            $merchant->getOauthClientId(),
            [self::MERCHANT_AUTH_ROLE],
            null,
            null
        );
    }

    private function authenticateAsMerchantUser(string $oauthUserId, string $credentials, ?string $email): UserInterface
    {
        $merchantUser = $this->merchantUserRepository->getOneByUserId($oauthUserId);

        if (!$merchantUser) {
            throw new AuthenticationException();
        }

        $symfonyRoles = $this->convertMerchantUserPermissionsToSecurityRoles(
            $this->merchantUserPermissionsService->resolveUserRole($merchantUser)->getPermissions()
        );
        $symfonyRoles[] = self::MERCHANT_USER_AUTH_ROLE;

        return new User(
            $merchantUser->getMerchantId(),
            $merchantUser->getUserId(),
            $credentials,
            $symfonyRoles,
            null,
            $email
        );
    }
}
