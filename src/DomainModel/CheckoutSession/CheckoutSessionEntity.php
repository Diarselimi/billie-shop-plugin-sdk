<?php

namespace App\DomainModel\CheckoutSession;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

/**
 * @deprecated use CheckoutSession
 */
class CheckoutSessionEntity extends AbstractTimestampableEntity
{
    private $uuid;

    private $merchantId;

    private $merchantDebtorExternalId;

    private $isActive;

    public static function fromNewModel(CheckoutSession $session): self
    {
        $self = new self();

        $self->uuid = (string) $session->token();
        $self->merchantId = $session->merchantId();
        $self->merchantDebtorExternalId = 'nonexistent'; // TODO
        $self->isActive = $session->isActive();

        return $self;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): CheckoutSessionEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): CheckoutSessionEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getMerchantDebtorExternalId(): string
    {
        return $this->merchantDebtorExternalId;
    }

    public function setMerchantDebtorExternalId(string $merchantDebtorExternalId): CheckoutSessionEntity
    {
        $this->merchantDebtorExternalId = $merchantDebtorExternalId;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): CheckoutSessionEntity
    {
        $this->isActive = $isActive;

        return $this;
    }
}
