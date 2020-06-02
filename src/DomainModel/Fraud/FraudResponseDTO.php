<?php

declare(strict_types=1);

namespace App\DomainModel\Fraud;

class FraudResponseDTO
{
    private $isFraud;

    public function __construct(bool $isFraud)
    {
        $this->isFraud = $isFraud;
    }

    public function isFraud(): bool
    {
        return $this->isFraud;
    }
}
