<?php

namespace App\DomainModel\Invoice;

use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ramsey\Uuid\UuidInterface;

interface InvoiceRequest
{
    public function getAmount(): TaxedMoney;

    public function getInvoiceUuid(): UuidInterface;

    public function getBillingDate(): \DateTimeInterface;

    public function getExternalCode(): string;

    public function getShippingInfo(): ?ShippingInfo;

    /** @deprecated */
    public function getShippingDocumentUrl(): ?string;
}
