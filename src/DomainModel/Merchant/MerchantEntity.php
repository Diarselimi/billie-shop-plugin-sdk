<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantEntity", type="object", properties={
 *      @OA\Property(property="id", type="integer"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="updated_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="available_financing_limit", type="number", format="float"),
 *      @OA\Property(property="api_key", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="company_id", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="payment_merchant_id", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="is_active", type="boolean"),
 *      @OA\Property(property="webhook_url", type="string", format="uri", nullable=true),
 *      @OA\Property(property="webhook_authorization", type="string", nullable=true, example="Authorization: Basic test"),
 * })
 */
class MerchantEntity extends AbstractTimestampableEntity implements ArrayableInterface
{
    private $name;

    private $financingPower;

    private $financingLimit;

    private $apiKey;

    private $companyId;

    private $paymentMerchantId;

    private $isActive;

    private $webhookUrl;

    private $webhookAuthorization;

    private $oauthClientId;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MerchantEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getFinancingPower(): float
    {
        return $this->financingPower;
    }

    public function setFinancingPower(float $financingPower): MerchantEntity
    {
        $this->financingPower = $financingPower;

        return $this;
    }

    public function getFinancingLimit(): ?float
    {
        return $this->financingLimit;
    }

    public function setFinancingLimit(float $financingLimit): MerchantEntity
    {
        $this->financingLimit = $financingLimit;

        return $this;
    }

    public function increaseFinancingLimit(float $difference): void
    {
        if ($this->getFinancingPower() > $this->getFinancingLimit()) {
            $this->financingLimit = $this->getFinancingPower();
        }
        $this->financingPower = $this->financingPower + $difference;
    }

    public function reduceFinancingLimit(float $difference): void
    {
        $newLimit = $this->financingPower - $difference;
        if ($newLimit < 0) {
            throw new MerchantDebtorLimitsException('Trying to set negative merchant financing limit');
        }

        $this->financingPower = $newLimit;
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

    public function getOauthClientId(): ? string
    {
        return $this->oauthClientId;
    }

    public function setOauthClientId(?string $oauthClientId): MerchantEntity
    {
        $this->oauthClientId = $oauthClientId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'financing_power' => $this->getFinancingPower(),
            'financing_limit' => $this->getFinancingLimit(),
            'api_key' => $this->getApiKey(),
            'company_id' => $this->getCompanyId(),
            'payment_merchant_id' => $this->getPaymentMerchantId(),
            'is_active' => $this->isActive(),
            'webhook_url' => $this->getWebhookUrl(),
            'webhook_authorization' => $this->getWebhookAuthorization(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'), // TODO: constant somewhere?
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
