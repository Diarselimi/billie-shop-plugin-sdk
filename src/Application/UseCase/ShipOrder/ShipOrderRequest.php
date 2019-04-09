<?php

namespace App\Application\UseCase\ShipOrder;

class ShipOrderRequest
{
    private $orderId;

    private $externalCode;

    private $merchantId;

    private $invoiceNumber;

    private $invoiceUrl;

    private $proofOfDeliveryUrl;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): ShipOrderRequest
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getExternalCode(): ? string
    {
        return $this->externalCode;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceUrl(): string
    {
        return $this->invoiceUrl;
    }

    public function getProofOfDeliveryUrl(): ?string
    {
        return $this->proofOfDeliveryUrl;
    }

    public function setExternalCode(?string $externalCode): ShipOrderRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function setMerchantId(int $merchantId): ShipOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function setInvoiceNumber(string $invoiceNumber): ShipOrderRequest
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function setInvoiceUrl(string $invoiceUrl): ShipOrderRequest
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function setProofOfDeliveryUrl(?string $proofOfDeliveryUrl): ShipOrderRequest
    {
        $this->proofOfDeliveryUrl = $proofOfDeliveryUrl;

        return $this;
    }
}
