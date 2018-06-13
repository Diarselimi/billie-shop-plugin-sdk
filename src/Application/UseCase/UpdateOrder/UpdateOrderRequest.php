<?php

namespace App\Application\UseCase\UpdateOrder;

class UpdateOrderRequest
{
    private $externalCode;
    private $invoiceNumber;
    private $invoiceUrl;
    private $merchantId;
    private $amountGross;
    private $amountNet;
    private $amountTax;
    private $duration;

    public function __construct(string $externalCode)
    {
        $this->externalCode = $externalCode;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl)
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId): UpdateOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getAmountNet(): ?float
    {
        return $this->amountNet;
    }

    public function setAmountNet(?float $amount): UpdateOrderRequest
    {
        $this->amountNet = $amount;

        return $this;
    }

    public function getAmountGross(): ?float
    {
        return $this->amountGross;
    }

    public function setAmountGross(?float $amount): UpdateOrderRequest
    {
        $this->amountGross = $amount;

        return $this;
    }

    public function getAmountTax(): ?float
    {
        return $this->amountTax;
    }

    public function setAmountTax(?float $amount): UpdateOrderRequest
    {
        $this->amountTax = $amount;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): UpdateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }
}
