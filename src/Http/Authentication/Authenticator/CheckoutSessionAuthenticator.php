<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Http\Authentication\CheckoutUser;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CheckoutSessionAuthenticator extends AbstractAuthenticator
{
    private $checkoutSessionRepository;

    public function __construct(
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        parent::__construct($merchantRepository);
    }

    public function supports(Request $request)
    {
        return $request->attributes->has(HttpConstantsInterface::REQUEST_ATTRIBUTE_CHECKOUT_SESSION_ID);
    }

    public function getCredentials(Request $request)
    {
        return $request->attributes->get(HttpConstantsInterface::REQUEST_ATTRIBUTE_CHECKOUT_SESSION_ID);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $checkoutSession = $this->checkoutSessionRepository->findOneByUuid($credentials);

        if (!$checkoutSession || !$checkoutSession->isActive()) {
            throw new AuthenticationException();
        }

        $merchant = $this->getActiveMerchantOrFail($checkoutSession->getMerchantId());

        return new CheckoutUser($merchant, $checkoutSession);
    }
}
