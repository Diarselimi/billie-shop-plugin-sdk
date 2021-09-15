<?php

namespace App\DomainModel\MerchantDebtorResponse;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantDebtorExtendedResponse",
 *     allOf={@OA\Schema(ref="#/components/schemas/MerchantDebtorResponse")},
 *     type="object",
 *     properties={
 *          @OA\Property(property="merchant_debtor_id", type="integer"),
 *          @OA\Property(property="company_id", type="integer"),
 *          @OA\Property(property="company_uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="payment_id", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="is_whitelisted", type="boolean"),
 *          @OA\Property(property="is_blacklisted", type="boolean"),
 *          @OA\Property(property="is_trusted_source", type="boolean"),
 *          @OA\Property(property="crefo_id", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="schufa_id", ref="#/components/schemas/TinyText", nullable=true),
 * })
 */
class MerchantDebtorExtended extends MerchantDebtor
{
    /**
     * @var int
     */
    private $merchantDebtorId;

    /**
     * @var string
     */
    private $companyId;

    /**
     * @var string
     */
    private $companyUuid;

    /**
     * @var string
     */
    private $paymentId;

    /**
     * @var boolean
     */
    private $isTrustedSource;

    /**
     * @var string
     */
    private $crefoId;

    /**
     * @var string
     */
    private $schufaId;

    public function getMerchantDebtorId(): int
    {
        return $this->merchantDebtorId;
    }

    /**
     * @param  int                           $merchantDebtorId
     * @return MerchantDebtorExtended|static
     */
    public function setMerchantDebtorId(int $merchantDebtorId): MerchantDebtorExtended
    {
        $this->merchantDebtorId = $merchantDebtorId;

        return $this;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * @param  string                        $companyId
     * @return MerchantDebtorExtended|static
     */
    public function setCompanyId(string $companyId): MerchantDebtorExtended
    {
        $this->companyId = $companyId;

        return $this;
    }

    public function getCompanyUuid(): string
    {
        return $this->companyUuid;
    }

    public function setCompanyUuid(string $companyUuid): MerchantDebtorExtended
    {
        $this->companyUuid = $companyUuid;

        return $this;
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @param  string                        $paymentId
     * @return MerchantDebtorExtended|static
     */
    public function setPaymentId(string $paymentId): MerchantDebtorExtended
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function isTrustedSource(): bool
    {
        return $this->isTrustedSource;
    }

    /**
     * @param  bool                          $isTrustedSource
     * @return MerchantDebtorExtended|static
     */
    public function setIsTrustedSource(bool $isTrustedSource): MerchantDebtorExtended
    {
        $this->isTrustedSource = $isTrustedSource;

        return $this;
    }

    public function getCrefoId(): ?string
    {
        return $this->crefoId;
    }

    /**
     * @param  string                        $crefoId
     * @return MerchantDebtorExtended|static
     */
    public function setCrefoId(?string $crefoId): MerchantDebtorExtended
    {
        $this->crefoId = $crefoId;

        return $this;
    }

    public function getSchufaId(): ?string
    {
        return $this->schufaId;
    }

    /**
     * @param  string                        $schufaId
     * @return MerchantDebtorExtended|static
     */
    public function setSchufaId(?string $schufaId): MerchantDebtorExtended
    {
        $this->schufaId = $schufaId;

        return $this;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        return array_merge($data, [
            'merchant_debtor_id' => $this->merchantDebtorId,
            'company_id' => (int) $this->companyId,
            'company_uuid' => $this->companyUuid,
            'payment_id' => $this->paymentId,
            'is_trusted_source' => $this->isTrustedSource,
            'crefo_id' => $this->crefoId,
            'schufa_id' => $this->schufaId,
        ]);
    }
}
