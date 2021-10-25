<?php

declare(strict_types=1);

namespace App\Application\UseCase\ModifyPartnerExternalData;

class ModifyPartnerExternalDataCommand
{
    private string $orderUuid;

    private string $merchantReference1;

    private ?string $merchantReference2;

    public function __construct(string $orderUuid, string $merchantReference1, ?string $merchantReference2)
    {
        $this->orderUuid = $orderUuid;
        $this->merchantReference1 = $merchantReference1;
        $this->merchantReference2 = $merchantReference2;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
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
