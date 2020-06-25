<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ActiveCheckoutSessionAuthenticator extends ExistingCheckoutSessionAuthenticator
{
    protected function validateSession(?CheckoutSessionEntity $checkoutSession): void
    {
        if (($checkoutSession === null) || !$checkoutSession->isActive()) {
            throw new AuthenticationException();
        }
    }
}
