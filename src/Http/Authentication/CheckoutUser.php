<?php

namespace App\Http\Authentication;

use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\Merchant\MerchantEntity;

class CheckoutUser extends AbstractUser
{
    public const AUTH_ROLE = 'ROLE_AUTHENTICATED_AS_CHECKOUT_USER';

    private CheckoutSession $checkoutSession;

    public function __construct(MerchantEntity $merchant, CheckoutSession $checkoutSession)
    {
        parent::__construct($merchant);
        $this->checkoutSession = $checkoutSession;
    }

    public function getCheckoutSession(): CheckoutSession
    {
        return $this->checkoutSession;
    }

    public function getRoles(): array
    {
        return [self::AUTH_ROLE];
    }
}
