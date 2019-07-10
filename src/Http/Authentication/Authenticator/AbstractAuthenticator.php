<?php

namespace App\Http\Authentication\Authenticator;

use App\Http\ApiError\ApiError;
use App\Http\ApiError\ApiErrorResponse;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

abstract class AbstractAuthenticator extends AbstractGuardAuthenticator
{
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new ApiErrorResponse(
            [new ApiError('Access denied', ApiError::CODE_FORBIDDEN)],
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
        $request->attributes->set(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID, $token->getUser()->getMerchantId());

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
}
