<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Http\ApiError\ApiError;
use App\Http\ApiError\ApiErrorResponse;
use App\Http\Authentication\AbstractUser;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

abstract class AbstractAuthenticator extends AbstractGuardAuthenticator
{
    protected $merchantRepository;

    public function __construct(MerchantRepositoryInterface $merchantRepository)
    {
        $this->merchantRepository = $merchantRepository;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new ApiErrorResponse(
            [new ApiError('Access Denied.', ApiError::CODE_FORBIDDEN)],
            ApiErrorResponse::HTTP_FORBIDDEN
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new ApiErrorResponse(
            [new ApiError('Unauthorized', ApiError::CODE_UNAUTHORIZED)],
            ApiErrorResponse::HTTP_UNAUTHORIZED
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();

        if ($user instanceof AbstractUser) {
            $request->attributes->set(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID, $user->getMerchant()->getId());
        }

        return null;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function supportsRememberMe()
    {
        return false;
    }

    protected function wasAlreadyAuthenticated(Request $request): bool
    {
        return $request->attributes->has(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
    }

    protected function assertValidMerchant($merchant): MerchantEntity
    {
        if (!($merchant instanceof MerchantEntity) || !$merchant->isActive()) {
            throw new AuthenticationException();
        }

        return $merchant;
    }
}
