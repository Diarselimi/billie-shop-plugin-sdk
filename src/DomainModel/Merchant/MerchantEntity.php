<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\Support\DateFormat;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use OpenApi\Annotations as OA;
use Ozean12\Money\Money;

/**
 * @OA\Schema(schema="MerchantEntity", type="object", properties={
 *      @OA\Property(property="id", type="integer"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="updated_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="available_financing_limit", type="number", format="float"),
 *      @OA\Property(property="api_key", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="company_id", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="company_uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="payment_merchant_id", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="sepa_b2b_document_uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="is_active", type="boolean"),
 *      @OA\Property(property="webhook_url", type="string", format="uri", nullable=true),
 *      @OA\Property(property="webhook_authorization", type="string", nullable=true, example="Authorization: Basic test"),
 *      @OA\Property(property="credentials", type="object", nullable=true, properties={
 *          @OA\Property(property="client_id", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="secret", type="string", example="234ty5uykjhfgeqw343ytreg")
 *      }),
 * })
 */
class MerchantEntity extends AbstractTimestampableEntity implements ArrayableInterface
{
    private $name;

    private $companyUuid;

    private $financingPower;

    private $financingLimit;

    private $apiKey;

    private $companyId;

    private $paymentUuid;

    private $sepaB2BDocumentUuid;

    private $sandboxPaymentUuid;

    private $isActive;

    private $webhookUrl;

    private $webhookAuthorization;

    private $oauthClientId;

    private $investorUuid;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MerchantEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getCompanyUuid(): string
    {
        return $this->companyUuid;
    }

    public function setCompanyUuid(string $companyUuid): MerchantEntity
    {
        $this->companyUuid = $companyUuid;

        return $this;
    }

    public function getFinancingPower(): Money
    {
        return $this->financingPower;
    }

    public function setFinancingPower(Money $financingPower): MerchantEntity
    {
        $this->financingPower = $financingPower;

        return $this;
    }

    public function getFinancingLimit(): Money
    {
        return $this->financingLimit ?? new Money(0);
    }

    public function setFinancingLimit(Money $financingLimit): MerchantEntity
    {
        $this->financingLimit = $financingLimit;

        return $this;
    }

    public function increaseFinancingLimit(Money $difference): void
    {
        if ($this->getFinancingPower()->greaterThan($this->getFinancingLimit())) {
            $this->setFinancingPower($this->getFinancingPower());
        }

        $this->setFinancingPower($this->getFinancingPower()->add($difference));
    }

    public function reduceFinancingLimit(Money $difference): void
    {
        $newLimit = $this->getFinancingPower()->subtract($difference);
        if ($newLimit->lessThan(0)) {
            throw new MerchantDebtorLimitsException('Trying to set negative merchant financing limit');
        }

        $this->setFinancingPower($newLimit);
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

    public function getSepaB2BDocumentUuid(): ?string
    {
        return $this->sepaB2BDocumentUuid;
    }

    public function setSepaB2BDocumentUuid(?string $sepaB2BDocumentUuid): MerchantEntity
    {
        $this->sepaB2BDocumentUuid = $sepaB2BDocumentUuid;

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

    public function getPaymentUuid(): ?string
    {
        return $this->paymentUuid;
    }

    public function setPaymentUuid(?string $paymentUuid): MerchantEntity
    {
        $this->paymentUuid = $paymentUuid;

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

    public function getSandboxPaymentUuid(): ?string
    {
        return $this->sandboxPaymentUuid;
    }

    public function setSandboxPaymentUuid(?string $sandboxPaymentUuid): MerchantEntity
    {
        $this->sandboxPaymentUuid = $sandboxPaymentUuid;

        return $this;
    }

    public function getInvestorUuid(): string
    {
        return $this->investorUuid;
    }

    public function setInvestorUuid(string $investorUuid): MerchantEntity
    {
        $this->investorUuid = $investorUuid;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'financing_power' => $this->getFinancingPower()->getMoneyValue(),
            'financing_limit' => $this->getFinancingLimit()->getMoneyValue(),
            'api_key' => $this->getApiKey(),
            'company_id' => $this->getCompanyId(),
            'company_uuid' => $this->getCompanyUuid(),
            'payment_merchant_id' => $this->getPaymentUuid(),
            'is_active' => $this->isActive(),
            'webhook_url' => $this->getWebhookUrl(),
            'webhook_authorization' => $this->getWebhookAuthorization(),
            'investor_uuid' => $this->getInvestorUuid(),
            'created_at' => $this->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
            'updated_at' => $this->getUpdatedAt()->format(DateFormat::FORMAT_YMD_HIS),
        ];
    }
}
