<?php

namespace App\Http\Authentication\Authenticator;

use App\Http\Authentication\MerchantApiUser;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request)
    {
        if ($this->wasAlreadyAuthenticated($request)) {
            return false;
        }

        return $request->headers->has(HttpConstantsInterface::REQUEST_HEADER_API_KEY);
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_KEY);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $merchant = $this->getActiveMerchantOrFail(null, $credentials);

        return new MerchantApiUser($merchant);
    }
}
