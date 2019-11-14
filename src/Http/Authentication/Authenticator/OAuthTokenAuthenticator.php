<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\Http\Authentication\MerchantApiUser;
use App\Http\Authentication\MerchantUser;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthTokenAuthenticator extends AbstractAuthenticator
{
    private $authenticationService;

    private $merchantUserRepository;

    private $merchantUserPermissionsService;

    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserPermissionsService $merchantUserPermissionsService
    ) {
        $this->authenticationService = $authenticationService;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserPermissionsService = $merchantUserPermissionsService;

        parent::__construct($merchantRepository);
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
            return $this->authenticateAsMerchantUser($tokenMetadata->getUserId(), $tokenMetadata->getEmail());
        }

        if ($tokenMetadata->getClientId()) {
            return $this->authenticateAsMerchantClient($tokenMetadata->getClientId());
        }

        throw new AuthenticationException();
    }

    private function authenticateAsMerchantClient(string $oauthClientId): UserInterface
    {
        $merchant = $this->getActiveMerchantOrFail(null, null, $oauthClientId);

        return new MerchantApiUser($merchant);
    }

    private function authenticateAsMerchantUser(string $oauthUserId, ?string $email): UserInterface
    {
        $userEntity = $this->merchantUserRepository->getOneByUuid($oauthUserId);

        if (!$userEntity) {
            throw new AuthenticationException();
        }

        $merchant = $this->getActiveMerchantOrFail($userEntity->getMerchantId());
        $permissions = $this->merchantUserPermissionsService->resolveUserRole($userEntity)->getPermissions();

        return new MerchantUser($merchant, $email, $userEntity, $permissions);
    }
}
