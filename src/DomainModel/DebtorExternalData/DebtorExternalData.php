<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorExternalData;

class DebtorExternalData
{
    private string $companyName;

    private ?string $merchantCustomerId = null;

    public function __construct(string $companyName, ?string $merchantCustomerId)
    {
        $this->companyName = $companyName;
        $this->merchantCustomerId = $merchantCustomerId;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getMerchantCustomerId(): ?string
    {
        return $this->merchantCustomerId;
    }
}
