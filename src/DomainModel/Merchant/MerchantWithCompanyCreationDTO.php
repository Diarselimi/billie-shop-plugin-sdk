<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\DebtorCompany\DebtorCreationDTO;
use Symfony\Component\Validator\Constraints as Assert;

class MerchantWithCompanyCreationDTO extends DebtorCreationDTO
{
    /**
     * @Assert\Regex("/^\d+(\.\d{0,2})?$/", message="The number should have have maximum 2 numbers after decimal.")
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $merchantFinancingLimit;

    /**
     * @Assert\Regex("/^\d+(\.\d{0,2})?$/", message="The number should have have maximum 2 numbers after decimal.")
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $initialDebtorFinancingLimit;

    /**
     * @Assert\Url()
     */
    private $webhookUrl;

    /**
     * @Assert\Type(type="string")
     */
    private $webhookAuthorization;

    /**
     * @Assert\Type(type="bool")
     */
    private $isOnboardingComplete;

    public function getMerchantFinancingLimit(): float
    {
        return $this->merchantFinancingLimit;
    }

    public function setMerchantFinancingLimit($merchantFinancingLimit): MerchantWithCompanyCreationDTO
    {
        $this->merchantFinancingLimit = $merchantFinancingLimit;

        return $this;
    }

    public function getInitialDebtorFinancingLimit(): float
    {
        return $this->initialDebtorFinancingLimit;
    }

    public function setInitialDebtorFinancingLimit($initialDebtorFinancingLimit): MerchantWithCompanyCreationDTO
    {
        $this->initialDebtorFinancingLimit = $initialDebtorFinancingLimit;

        return $this;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl($webhookUrl): MerchantWithCompanyCreationDTO
    {
        $this->webhookUrl = $webhookUrl;

        return $this;
    }

    public function getWebhookAuthorization(): ?string
    {
        return $this->webhookAuthorization;
    }

    public function setWebhookAuthorization($webhookAuthorization): MerchantWithCompanyCreationDTO
    {
        $this->webhookAuthorization = $webhookAuthorization;

        return $this;
    }

    public function isOnboardingComplete(): bool
    {
        return $this->isOnboardingComplete;
    }

    public function setIsOnboardingComplete($isOnboardingComplete): MerchantWithCompanyCreationDTO
    {
        $this->isOnboardingComplete = $isOnboardingComplete;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'merchant_financing_limit' => $this->getMerchantFinancingLimit(),
            'initial_debtor_financing_limit' => $this->getInitialDebtorFinancingLimit(),
            'webhook_url' => $this->getWebhookUrl(),
            'webhook_authorization' => $this->getWebhookAuthorization(),
            'is_onboarding_complete' => $this->isOnboardingComplete(),
        ]);
    }
}
