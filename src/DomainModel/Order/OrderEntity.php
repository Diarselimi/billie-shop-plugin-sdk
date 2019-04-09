<?php

namespace App\DomainModel\Order;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class OrderEntity extends AbstractTimestampableEntity
{
    private const STATE_LATE = 'late';

    const MAX_DURATION_IN_WAITING_STATE = '9 days';

    private $uuid;

    private $amountNet;

    private $amountGross;

    private $amountTax;

    private $duration;

    private $externalCode;

    private $state;

    private $externalComment;

    private $internalComment;

    private $invoiceNumber;

    private $invoiceUrl;

    private $proofOfDeliveryUrl;

    private $merchantDebtorId;

    private $merchantId;

    private $deliveryAddressId;

    private $debtorPersonId;

    private $debtorExternalDataId;

    private $paymentId;

    private $shippedAt;

    private $markedAsFraudAt;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): OrderEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    public function setAmountNet(float $amountNet): OrderEntity
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getAmountGross(): float
    {
        return $this->amountGross;
    }

    public function setAmountGross(float $amountGross): OrderEntity
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getAmountTax(): float
    {
        return $this->amountTax;
    }

    public function setAmountTax(float $amountTax): OrderEntity
    {
        $this->amountTax = $amountTax;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): OrderEntity
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode(): ? string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): OrderEntity
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): OrderEntity
    {
        $this->state = $state;

        return $this;
    }

    public function isLate(): bool
    {
        return $this->getState() === self::STATE_LATE;
    }

    public function getExternalComment(): ?string
    {
        return $this->externalComment;
    }

    public function setExternalComment(?string $externalComment): OrderEntity
    {
        $this->externalComment = $externalComment;

        return $this;
    }

    public function getInternalComment(): ?string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): OrderEntity
    {
        $this->internalComment = $internalComment;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): OrderEntity
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): OrderEntity
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getProofOfDeliveryUrl(): ?string
    {
        return $this->proofOfDeliveryUrl;
    }

    public function setProofOfDeliveryUrl(?string $proofOfDeliveryUrl): OrderEntity
    {
        $this->proofOfDeliveryUrl = $proofOfDeliveryUrl;

        return $this;
    }

    public function getMerchantDebtorId(): ?int
    {
        return $this->merchantDebtorId;
    }

    public function setMerchantDebtorId(?int $merchantDebtorId): OrderEntity
    {
        $this->merchantDebtorId = $merchantDebtorId;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): OrderEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDeliveryAddressId(): int
    {
        return $this->deliveryAddressId;
    }

    public function setDeliveryAddressId(int $deliveryAddressId): OrderEntity
    {
        $this->deliveryAddressId = $deliveryAddressId;

        return $this;
    }

    public function getDebtorPersonId(): int
    {
        return $this->debtorPersonId;
    }

    public function setDebtorPersonId(int $debtorPersonId): OrderEntity
    {
        $this->debtorPersonId = $debtorPersonId;

        return $this;
    }

    public function getDebtorExternalDataId(): ?int
    {
        return $this->debtorExternalDataId;
    }

    public function setDebtorExternalDataId(int $debtorExternalDataId): OrderEntity
    {
        $this->debtorExternalDataId = $debtorExternalDataId;

        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): OrderEntity
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getShippedAt(): ?\DateTime
    {
        return $this->shippedAt;
    }

    public function setShippedAt(\DateTime $shippedAt = null): OrderEntity
    {
        $this->shippedAt = $shippedAt;

        return $this;
    }

    public function getMarkedAsFraudAt(): ?\DateTime
    {
        return $this->markedAsFraudAt;
    }

    public function setMarkedAsFraudAt(\DateTime $markedAsFraudAt = null): OrderEntity
    {
        $this->markedAsFraudAt = $markedAsFraudAt;

        return $this;
    }
}
