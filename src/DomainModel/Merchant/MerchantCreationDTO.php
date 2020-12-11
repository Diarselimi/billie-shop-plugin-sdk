<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;

class MerchantCreationDTO
{
    private $company;

    private $apiKey;

    private $paymentUuid;

    private $merchantFinancingLimit;

    private $initialDebtorFinancingLimit;

    private $webhookUrl;

    private $webhookAuthorization;

    private $merchant;

    private $oauthClient;

    private $isOnboardingComplete;

    private $feeRates = [];

    public function __construct(
        DebtorCompany $company,
        string $apiKey,
        string $paymentUuid,
        float $merchantFinancingLimit,
        float $initialDebtorFinancingLimit
    ) {
        $this->company = $company;
        $this->apiKey = $apiKey;
        $this->paymentUuid = $paymentUuid;
        $this->merchantFinancingLimit = $merchantFinancingLimit;
        $this->initialDebtorFinancingLimit = $initialDebtorFinancingLimit;
    }

    public function getCompany(): DebtorCompany
    {
        return $this->company;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getPaymentUuid(): string
    {
        return $this->paymentUuid;
    }

    public function getMerchantFinancingLimit(): float
    {
        return $this->merchantFinancingLimit;
    }

    public function getInitialDebtorFinancingLimit(): float
    {
        return $this->initialDebtorFinancingLimit;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;

        return $this;
    }

    public function getWebhookAuthorization(): ?string
    {
        return $this->webhookAuthorization;
    }

    public function setWebhookAuthorization(?string $webhookAuthorization)
    {
        $this->webhookAuthorization = $webhookAuthorization;

        return $this;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function setMerchant(MerchantEntity $merchant): MerchantCreationDTO
    {
        $this->merchant = $merchant;

        return $this;
    }

    public function getOauthClient(): AuthenticationServiceCreateClientResponseDTO
    {
        return $this->oauthClient;
    }

    public function setOauthClient(AuthenticationServiceCreateClientResponseDTO $oauthClient): MerchantCreationDTO
    {
        $this->oauthClient = $oauthClient;

        return $this;
    }

    public function isOnboardComplete(): bool
    {
        return $this->isOnboardingComplete;
    }

    public function setIsOnboardingComplete(bool $isOnboardingComplete): MerchantCreationDTO
    {
        $this->isOnboardingComplete = $isOnboardingComplete;

        return $this;
    }

    public function getFeeRates(): array
    {
        return $this->feeRates;
    }

    public function setFeeRates(array $feeRates): MerchantCreationDTO
    {
        $this->feeRates = $feeRates;

        return $this;
    }
}
