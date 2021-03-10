<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoicePayments;

final class GetInvoicePaymentsRequest
{
    private string $invoiceUuid;

    private int $merchantId;

    public function __construct(string $invoiceUuid, int $merchantId)
    {
        $this->invoiceUuid = $invoiceUuid;
        $this->merchantId = $merchantId;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
