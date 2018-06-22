<?php

namespace App\Application\UseCase\ShipOrder;

class ShipOrderRequest
{
    private $externalCode;
    private $customerId;
    private $invoiceNumber;
    private $invoiceUrl;
    private $proofOfDeliveryUrl;

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
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

    public function setExternalCode(string $externalCode): ShipOrderRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function setCustomerId(int $customerId): ShipOrderRequest
    {
        $this->customerId = $customerId;

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
