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
 *      @OA\Property(property="roles", type="array", @OA\Items(type="string", example=\App\DomainModel\Merchant\MerchantEntity::ROLE_API_USER)),
 *      @OA\Property(property="is_active", type="boolean"),
 *      @OA\Property(property="webhook_url", type="string", format="uri", nullable=true),
 *      @OA\Property(property="webhook_authorization", type="string", nullable=true, example="Authorization: Basic test"),
 * })
 */
class MerchantEntity extends AbstractTimestampableEntity implements ArrayableInterface
{
    public const ROLE_API_USER = 'ROLE_API_USER';

    public const DEFAULT_ROLES = [
        self::ROLE_API_USER,
    ];

    private $name;

    private $availableFinancingLimit;

    private $apiKey;

    private $companyId;

    private $paymentMerchantId;

    private $roles;

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

    public function getAvailableFinancingLimit(): float
    {
        return $this->availableFinancingLimit;
    }

    public function setAvailableFinancingLimit(float $availableFinancingLimit): MerchantEntity
    {
        $this->availableFinancingLimit = $availableFinancingLimit;

        return $this;
    }

    public function increaseAvailableFinancingLimit(float $difference): void
    {
        $this->availableFinancingLimit = $this->availableFinancingLimit + $difference;
    }

    public function reduceAvailableFinancingLimit(float $difference): void
    {
        $newLimit = $this->availableFinancingLimit - $difference;
        if ($newLimit < 0) {
            throw new MerchantDebtorLimitsException('Trying to set negative merchant financing limit');
        }

        $this->availableFinancingLimit = $newLimit;
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

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): MerchantEntity
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
