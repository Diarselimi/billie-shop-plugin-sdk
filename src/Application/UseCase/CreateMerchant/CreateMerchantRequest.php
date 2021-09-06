<?php

namespace App\Application\UseCase\CreateMerchant;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CreateMerchantRequest", title="Merchant Data", required={"company_id", "merchant_financing_limit", "initial_debtor_financing_limit"}, properties={
 *     @OA\Property(property="company_id", type="string", format="numeric", description="Company ID from webapp.companies"),
 *     @OA\Property(property="merchant_financing_limit", type="number", format="float"),
 *     @OA\Property(property="initial_debtor_financing_limit", type="number", format="float"),
 *     @OA\Property(property="webhook_url", type="string", format="uri"),
 *     @OA\Property(property="webhook_authorization", type="string", example="X-Api-Key: test",
 *          description="Authorization header that will be sent with the merchant webhooks. Currently `X-Api-Key: XXX` and `Authorization: Basic XXX` are supported."
 *     )
 * })
 */
class CreateMerchantRequest
{
    private $companyId;

    private $merchantFinancingLimit;

    private $initialDebtorFinancingLimit;

    private $webhookUrl;

    private $webhookAuthorization;

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    public function setCompanyId(string $companyId): CreateMerchantRequest
    {
        $this->companyId = $companyId;

        return $this;
    }

    public function getMerchantFinancingLimit(): float
    {
        return $this->merchantFinancingLimit;
    }

    public function setMerchantFinancingLimit(float $merchantFinancingLimit): CreateMerchantRequest
    {
        $this->merchantFinancingLimit = $merchantFinancingLimit;

        return $this;
    }

    public function getInitialDebtorFinancingLimit(): float
    {
        return $this->initialDebtorFinancingLimit;
    }

    public function setInitialDebtorFinancingLimit(float $initialDebtorFinancingLimit): CreateMerchantRequest
    {
        $this->initialDebtorFinancingLimit = $initialDebtorFinancingLimit;

        return $this;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl): CreateMerchantRequest
    {
        $this->webhookUrl = $webhookUrl;

        return $this;
    }

    public function getWebhookAuthorization(): ?string
    {
        return $this->webhookAuthorization;
    }

    public function setWebhookAuthorization(?string $webhookAuthorization): CreateMerchantRequest
    {
        $this->webhookAuthorization = $webhookAuthorization;

        return $this;
    }
}
