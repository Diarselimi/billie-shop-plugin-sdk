<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice;

class GetInvoiceRequest
{
    private string $uuid;

    private int $merchantId;

    public function __construct(string $uuid, int $merchantId)
    {
        $this->uuid = $uuid;
        $this->merchantId = $merchantId;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
