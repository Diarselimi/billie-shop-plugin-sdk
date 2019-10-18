<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Http\Authentication\User;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private $merchantRepository;

    public function __construct(MerchantRepositoryInterface $merchantRepository)
    {
        $this->merchantRepository = $merchantRepository;
    }

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
        $merchant = $this->merchantRepository->getOneByApiKey($credentials);

        if (!$merchant || !$merchant->isActive()) {
            throw new AuthenticationException();
        }

        return new User(
            $merchant->getId(),
            $merchant->getName(),
            $merchant->getApiKey(),
            [self::MERCHANT_AUTH_ROLE]
        );
    }
}
