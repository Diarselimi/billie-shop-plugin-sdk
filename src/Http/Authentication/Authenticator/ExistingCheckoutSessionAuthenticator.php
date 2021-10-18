<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\CheckoutSession\Token;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\Http\Authentication\CheckoutUser;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ExistingCheckoutSessionAuthenticator extends AbstractAuthenticator
{
    private $checkoutSessionRepository;

    public function __construct(
        CheckoutSessionRepository $checkoutSessionRepository,
        MerchantRepository $merchantRepository
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

    public function getUser($sessionToken, UserProviderInterface $userProvider)
    {
        $checkoutSession = $this->loadSession($sessionToken);
        $merchant = $this->loadMerchant($checkoutSession);

        return new CheckoutUser($merchant, $checkoutSession);
    }

    private function loadSession(string $sessionToken): CheckoutSession
    {
        $checkoutSession = $this->checkoutSessionRepository->findByToken(Token::fromHash($sessionToken));

        $this->validateSession($checkoutSession);

        return $checkoutSession;
    }

    protected function validateSession(?CheckoutSession $checkoutSession): void
    {
        if (null == $checkoutSession) {
            throw new AuthenticationException();
        }
    }

    private function loadMerchant(CheckoutSession $checkoutSession): MerchantEntity
    {
        $merchant = $this->merchantRepository->getOneById($checkoutSession->merchantId());
        $merchant = $this->assertValidMerchant($merchant);

        return $merchant;
    }
}
