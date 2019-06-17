<?php

namespace App\DomainModel\MerchantDebtorResponse;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantDebtorResponse", allOf={@OA\Schema(ref="#/components/schemas/AbstractMerchantDebtorResponse")}, type="object", properties={
 *      @OA\Property(property="address_street", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_house", ref="#/components/schemas/TinyText", nullable=true),
 *      @OA\Property(property="address_postal_code", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_city", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_country", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="outstanding_amount", type="number", format="float"),
 *      @OA\Property(property="outstanding_amount_created", type="number", format="float"),
 *      @OA\Property(property="outstanding_amount_late", type="number", format="float"),
 * })
 */
class MerchantDebtor extends AbstractMerchantDebtor
{
    private $addressStreet;

    private $addressHouse;

    private $addressPostalCode;

    private $addressCity;

    private $addressCountry;

    private $outstandingAmount;

    private $outstandingAmountCreated;

    private $outstandingAmountLate;

    public function getAddressStreet(): string
    {
        return $this->addressStreet;
    }

    /**
     * @param  string                $addressStreet
     * @return MerchantDebtor|static
     */
    public function setAddressStreet(string $addressStreet): MerchantDebtor
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressHouse(): string
    {
        return $this->addressHouse;
    }

    /**
     * @param  string                $addressHouse
     * @return MerchantDebtor|static
     */
    public function setAddressHouse(string $addressHouse): MerchantDebtor
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressPostalCode(): string
    {
        return $this->addressPostalCode;
    }

    /**
     * @param  string                $addressPostalCode
     * @return MerchantDebtor|static
     */
    public function setAddressPostalCode(string $addressPostalCode): MerchantDebtor
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCity(): string
    {
        return $this->addressCity;
    }

    /**
     * @param  string                $addressCity
     * @return MerchantDebtor|static
     */
    public function setAddressCity(string $addressCity): MerchantDebtor
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressCountry(): string
    {
        return $this->addressCountry;
    }

    /**
     * @param  string                $addressCountry
     * @return MerchantDebtor|static
     */
    public function setAddressCountry(string $addressCountry): MerchantDebtor
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getOutstandingAmount(): float
    {
        return $this->outstandingAmount;
    }

    /**
     * @param  float                 $outstandingAmount
     * @return MerchantDebtor|static
     */
    public function setOutstandingAmount(float $outstandingAmount): MerchantDebtor
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getOutstandingAmountCreated(): float
    {
        return $this->outstandingAmountCreated;
    }

    /**
     * @param  float                 $outstandingAmountCreated
     * @return MerchantDebtor|static
     */
    public function setOutstandingAmountCreated(float $outstandingAmountCreated): MerchantDebtor
    {
        $this->outstandingAmountCreated = $outstandingAmountCreated;

        return $this;
    }

    public function getOutstandingAmountLate(): float
    {
        return $this->outstandingAmountLate;
    }

    /**
     * @param  float                 $outstandingAmountLate
     * @return MerchantDebtor|static
     */
    public function setOutstandingAmountLate(float $outstandingAmountLate): MerchantDebtor
    {
        $this->outstandingAmountLate = $outstandingAmountLate;

        return $this;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        return array_merge($data, [
            'address_street' => $this->addressStreet,
            'address_house' => $this->addressHouse,
            'address_postal_code' => $this->addressPostalCode,
            'address_city' => $this->addressCity,
            'address_country' => $this->addressCountry,

            'outstanding_amount' => $this->outstandingAmount,
            'outstanding_amount_created' => $this->outstandingAmountCreated,
            'outstanding_amount_late' => $this->outstandingAmountLate,
        ]);
    }
}
