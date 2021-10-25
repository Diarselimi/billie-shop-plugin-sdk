<?php

declare(strict_types=1);

namespace App\DomainModel\PartnerMerchant;

class PartnerExternalData
{
    private string $merchantReference1;

    private ?string $merchantReference2;

    public function __construct(string $merchantReference1, ?string $merchantReference2)
    {
        $this->merchantReference1 = $merchantReference1;
        $this->merchantReference2 = $merchantReference2;
    }

    public function getMerchantReference1(): string
    {
        return $this->merchantReference1;
    }

    public function getMerchantReference2(): ?string
    {
        return $this->merchantReference2;
    }
}
