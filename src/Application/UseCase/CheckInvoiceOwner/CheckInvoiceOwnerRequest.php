<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckInvoiceOwner;

class CheckInvoiceOwnerRequest
{
    private int $merchantId;

    private string $invoiceUuid;

    public function __construct(int $merchantId, string $invoiceUuid)
    {
        $this->merchantId = $merchantId;
        $this->invoiceUuid = $invoiceUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }
}
