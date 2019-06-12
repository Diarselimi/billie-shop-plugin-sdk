<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="AbstractMerchantDebtorResponse", type="object", properties={
 *      @OA\Property(property="id", ref="#/components/schemas/UUID", description="Debtor UUID"),
 *      @OA\Property(property="external_code", ref="#/components/schemas/TinyText", description="Merchant Customer ID"),
 *      @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="financing_limit", type="number", format="float", description="Full limit assigned to this debtor."),
 *      @OA\Property(property="financing_power", type="number", format="float", description="Available limit for this debtor."),
 *      @OA\Property(property="bank_account_iban", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="bank_account_bic", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 * })
 */
abstract class AbstractMerchantDebtor implements ArrayableInterface
{
    private $uuid;

    private $externalCode;

    private $name;

    private $financingLimit;

    private $financingPower;

    private $createdAt;

    private $bankAccountIban;

    private $bankAccountBic;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param  string                        $uuid
     * @return AbstractMerchantDebtor|static
     */
    public function setUuid(string $uuid): AbstractMerchantDebtor
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    /**
     * @param  string                        $externalCode
     * @return AbstractMerchantDebtor|static
     */
    public function setExternalCode(string $externalCode): AbstractMerchantDebtor
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string                        $name
     * @return AbstractMerchantDebtor|static
     */
    public function setName(string $name): AbstractMerchantDebtor
    {
        $this->name = $name;

        return $this;
    }

    public function getFinancingLimit(): float
    {
        return $this->financingLimit;
    }

    /**
     * @param  float                         $financingLimit
     * @return AbstractMerchantDebtor|static
     */
    public function setFinancingLimit(float $financingLimit): AbstractMerchantDebtor
    {
        $this->financingLimit = $financingLimit;

        return $this;
    }

    public function getFinancingPower(): float
    {
        return $this->financingPower;
    }

    /**
     * @param  float                         $financingPower
     * @return AbstractMerchantDebtor|static
     */
    public function setFinancingPower(float $financingPower): AbstractMerchantDebtor
    {
        $this->financingPower = $financingPower;

        return $this;
    }

    public function getBankAccountIban(): string
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban(string $bankAccountIban): AbstractMerchantDebtor
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): string
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic(string $bankAccountBic): AbstractMerchantDebtor
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param  \DateTime                     $createdAt
     * @return AbstractMerchantDebtor|static
     */
    public function setCreatedAt(\DateTime $createdAt): AbstractMerchantDebtor
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->uuid,
            'external_code' => $this->externalCode,
            'name' => $this->name,

            'financing_limit' => $this->financingLimit,
            'financing_power' => $this->financingPower,

            'bank_account_iban' => $this->bankAccountIban,
            'bank_account_bic' => $this->bankAccountBic,

            'created_at' => $this->createdAt->format(\DateTime::ISO8601),
        ];
    }
}
