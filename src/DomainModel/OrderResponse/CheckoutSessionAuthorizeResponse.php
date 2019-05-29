<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\ArrayableInterface;

class CheckoutSessionAuthorizeResponse implements ArrayableInterface
{
    private $reasons;

    public function getReasons(): ?array
    {
        return $this->reasons;
    }

    public function setReasons(?array $reasons): CheckoutSessionAuthorizeResponse
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function toArray(): array
    {
        return ['reasons' => $this->getReasons()];
    }
}
