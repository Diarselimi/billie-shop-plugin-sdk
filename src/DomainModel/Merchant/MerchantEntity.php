<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\AbstractEntity;

class MerchantEntity extends AbstractEntity
{
    private $name;
    private $availableFinancingLimit;
    private $apiKey;
    private $companyId;
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
}
