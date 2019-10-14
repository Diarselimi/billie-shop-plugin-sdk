<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserEntity;
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

    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserRepositoryInterface  $merchantUserRepository
    ) {
        $this->authenticationService = $authenticationService;
        $this->merchantRepository = $merchantRepository;
        $this->merchantUserRepository = $merchantUserRepository;
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
            [MerchantUserEntity::ROLE_MERCHANT],
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

        return new User(
            $merchantUser->getMerchantId(),
            $merchantUser->getUserId(),
            $credentials,
            $merchantUser->getRoles(),
            null,
            $email
        );
    }
}
