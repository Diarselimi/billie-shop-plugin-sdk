<?php

namespace App\DomainModel\MerchantDebtorResponse;

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
    private $paymentId;

    /**
     * @var boolean
     */
    private $isWhitelisted;

    /**
     * @var boolean
     */
    private $isBlacklisted;

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

    public function isWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }

    /**
     * @param  bool                          $isWhitelisted
     * @return MerchantDebtorExtended|static
     */
    public function setIsWhitelisted(bool $isWhitelisted): MerchantDebtorExtended
    {
        $this->isWhitelisted = $isWhitelisted;

        return $this;
    }

    public function isBlacklisted(): bool
    {
        return $this->isBlacklisted;
    }

    /**
     * @param  bool                          $isBlacklisted
     * @return MerchantDebtorExtended|static
     */
    public function setIsBlacklisted(bool $isBlacklisted): MerchantDebtorExtended
    {
        $this->isBlacklisted = $isBlacklisted;

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

    public function getCrefoId(): string
    {
        return $this->crefoId;
    }

    /**
     * @param  string                        $crefoId
     * @return MerchantDebtorExtended|static
     */
    public function setCrefoId(string $crefoId): MerchantDebtorExtended
    {
        $this->crefoId = $crefoId;

        return $this;
    }

    public function getSchufaId(): string
    {
        return $this->schufaId;
    }

    /**
     * @param  string                        $schufaId
     * @return MerchantDebtorExtended|static
     */
    public function setSchufaId(string $schufaId): MerchantDebtorExtended
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
            'payment_id' => $this->paymentId,
            'is_whitelisted' => $this->isWhitelisted,
            'is_blacklisted' => $this->isBlacklisted,
            'is_trusted_source' => $this->isTrustedSource,
            'crefo_id' => $this->crefoId,
            'schufa_id' => $this->schufaId,
        ]);
    }
}
