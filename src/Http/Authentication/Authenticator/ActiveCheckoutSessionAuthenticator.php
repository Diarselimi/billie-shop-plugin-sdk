<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\CheckoutSession\CheckoutSession;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ActiveCheckoutSessionAuthenticator extends ExistingCheckoutSessionAuthenticator
{
    protected function validateSession(?CheckoutSession $checkoutSession): void
    {
        if (null !== $checkoutSession && $checkoutSession->isActive()) {
            return;
        }

        throw new AuthenticationException();
    }
}
