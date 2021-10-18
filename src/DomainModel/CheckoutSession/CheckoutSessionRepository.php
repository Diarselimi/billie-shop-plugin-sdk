<?php

namespace App\DomainModel\CheckoutSession;

interface CheckoutSessionRepository
{
    /**
     * @deprecated use findByToken
     */
    public function findById(int $id): ?CheckoutSession;

    public function findByToken(Token $token): ?CheckoutSession;

    public function save(CheckoutSession $checkoutSession): void;
}
