<?php

namespace App\DomainModel\CheckoutSession;

interface CheckoutSessionRepository
{
    public function save(CheckoutSession $checkoutSession): void;
}
