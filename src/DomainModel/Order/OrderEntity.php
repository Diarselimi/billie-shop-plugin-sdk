<?php

namespace App\DomainModel\Order;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityTrait;

class OrderEntity extends AbstractTimestampableEntity implements StatefulEntityInterface
{
    use StatefulEntityTrait;

    private const STATE_LATE = 'late';

    public const MAX_DURATION_IN_PRE_WAITING_STATE = '1 days';

    public const MAX_DURATION_IN_AUTHORIZED_STATE = '1 days';

    public const MAX_DURATION_IN_WAITING_STATE = '9 days';

    public const CREATION_SOURCE_API = 'api';

    public const CREATION_SOURCE_CHECKOUT = 'checkout';

    public const CREATION_SOURCE_DASHBOARD = 'dashboard';

    private const STATE_TRANSITION_ENTITY_CLASS = OrderStateTransitionEntity::class;

    private $uuid;

    private $amountForgiven;

    private $externalCode;

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

    private $checkoutSessionId;

    private $creationSource;

    private $companyBillingAddressUuid;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): OrderEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getAmountForgiven(): float
    {
        return $this->amountForgiven;
    }

    public function setAmountForgiven(float $amountForgiven): OrderEntity
    {
        $this->amountForgiven = $amountForgiven;

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

    public function getCheckoutSessionId(): ?int
    {
        return $this->checkoutSessionId;
    }

    public function setCheckoutSessionId(?int $checkoutSessionId): OrderEntity
    {
        $this->checkoutSessionId = $checkoutSessionId;

        return $this;
    }

    public function getCreationSource(): string
    {
        return $this->creationSource;
    }

    public function setCreationSource(string $creationSource): OrderEntity
    {
        $this->creationSource = $creationSource;

        return $this;
    }

    public function getStateTransitionEntityClass(): string
    {
        return self::STATE_TRANSITION_ENTITY_CLASS;
    }

    public function setCompanyBillingAddressUuid(?string $uuid): OrderEntity
    {
        $this->companyBillingAddressUuid = $uuid;

        return $this;
    }

    public function getCompanyBillingAddressUuid(): ?string
    {
        return $this->companyBillingAddressUuid;
    }
}
