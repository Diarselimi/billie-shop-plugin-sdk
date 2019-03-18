<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\ArrayableInterface;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantEntity extends AbstractTimestampableEntity implements ArrayableInterface
{
    private $name;

    private $availableFinancingLimit;

    private $apiKey;

    private $companyId;

    private $paymentMerchantId;

    private $roles;

    private $isActive;

    private $webhookUrl;

    private $webhookAuthorization;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MerchantEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getAvailableFinancingLimit(): float
    {
        return $this->availableFinancingLimit;
    }

    public function setAvailableFinancingLimit(float $availableFinancingLimit): MerchantEntity
    {
        $this->availableFinancingLimit = $availableFinancingLimit;

        return $this;
    }

    public function increaseAvailableFinancingLimit(float $delta): void
    {
        $this->availableFinancingLimit = $this->availableFinancingLimit + $delta;
    }

    public function reduceAvailableFinancingLimit(float $delta): void
    {
        $this->availableFinancingLimit = $this->availableFinancingLimit - $delta;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): MerchantEntity
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    public function setCompanyId(string $companyId): MerchantEntity
    {
        $this->companyId = $companyId;

        return $this;
    }

    public function getRoles(): string
    {
        return $this->roles;
    }

    public function setRoles(string $roles): MerchantEntity
    {
        $this->roles = $roles;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): MerchantEntity
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl): MerchantEntity
    {
        $this->webhookUrl = $webhookUrl;

        return $this;
    }

    public function getWebhookAuthorization(): ?string
    {
        return $this->webhookAuthorization;
    }

    public function setWebhookAuthorization(?string $webhookAuthorization): MerchantEntity
    {
        $this->webhookAuthorization = $webhookAuthorization;

        return $this;
    }

    public function getPaymentMerchantId(): ?string
    {
        return $this->paymentMerchantId;
    }

    public function setPaymentMerchantId(?string $paymentMerchantId): MerchantEntity
    {
        $this->paymentMerchantId = $paymentMerchantId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'available_financing_limit' => $this->getAvailableFinancingLimit(),
            'api_key' => $this->getApiKey(),
            'company_id' => $this->getCompanyId(),
            'payment_merchant_id' => $this->getPaymentMerchantId(),
            'roles' => $this->getRoles(),
            'is_active' => $this->isActive(),
            'webhook_url' => $this->getWebhookUrl(),
            'webhook_authorization' => $this->getWebhookAuthorization(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'), // TODO: constant somewhere?
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
