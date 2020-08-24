<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Http\Authentication\CheckoutUser;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ExistingCheckoutSessionAuthenticator extends AbstractAuthenticator
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
        return !empty($request->get(HttpConstantsInterface::REQUEST_ATTRIBUTE_CHECKOUT_SESSION_ID));
    }

    public function getCredentials(Request $request)
    {
        return $request->get(HttpConstantsInterface::REQUEST_ATTRIBUTE_CHECKOUT_SESSION_ID);
    }

    protected function validateSession(?CheckoutSessionEntity $checkoutSession): void
    {
        if ($checkoutSession === null) {
            throw new AuthenticationException();
        }
    }

    public function getUser($checkoutSessionId, UserProviderInterface $userProvider)
    {
        $checkoutSession = $this->checkoutSessionRepository->findOneByUuid($checkoutSessionId);
        $this->validateSession($checkoutSession);

        $merchant = $this->assertValidMerchant(
            $this->merchantRepository->getOneById($checkoutSession->getMerchantId())
        );

        return new CheckoutUser($merchant, $checkoutSession);
    }
}
