<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\ArrayableInterface;

abstract class AbstractMerchantDebtor implements ArrayableInterface
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $externalCode;

    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $financingLimit;

    /**
     * @var float
     */
    private $financingPower;

    /**
     * @var \DateTime
     */
    private $createdAt;

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

            'created_at' => $this->createdAt->format(\DateTime::ISO8601),
        ];
    }
}
