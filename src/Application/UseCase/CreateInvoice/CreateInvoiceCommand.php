<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateInvoice;

use App\DomainModel\Invoice\InvoiceRequest;
use App\DomainModel\Invoice\ShippingInfo;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ramsey\Uuid\UuidInterface;

class CreateInvoiceCommand implements InvoiceRequest
{
    private TaxedMoney $amount;

    private string $invoiceExternalCode;

    private string $orderId;

    private UuidInterface $invoiceUuid;

    private \DateTimeInterface $billingDate;

    private ?ShippingInfo $shippingInfo;

    public function __construct(
        string $orderId,
        int $cents,
        string $invoiceExternalCode,
        string $invoiceCapturedAt,
        UuidInterface $invoiceUuid,
        ?ShippingInfo $shippingInfo = null
    ) {
        $gross = new Money($cents / 100);
        $this->amount = new TaxedMoney($gross, $gross, new Money(0));
        $this->invoiceExternalCode = $invoiceExternalCode;
        $this->billingDate = new \DateTime($invoiceCapturedAt);
        $this->orderId = $orderId;
        $this->invoiceUuid = $invoiceUuid;
        $this->shippingInfo = $shippingInfo;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function getExternalCode(): string
    {
        return $this->invoiceExternalCode;
    }

    public function getBillingDate(): \DateTimeInterface
    {
        return $this->billingDate;
    }

    public function getInvoiceUuid(): UuidInterface
    {
        return $this->invoiceUuid;
    }

    public function getShippingInfo(): ?ShippingInfo
    {
        return $this->shippingInfo;
    }

    public function getShippingDocumentUrl(): ?string
    {
        return null;
    }
}
