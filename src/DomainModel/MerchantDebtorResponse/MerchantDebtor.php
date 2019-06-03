<?php

namespace App\DomainModel\MerchantDebtorResponse;

class MerchantDebtor extends AbstractMerchantDebtor
{
    /**
     * @var string
     */
    private $addressStreet;

    /**
     * @var string
     */
    private $addressHouse;

    /**
     * @var string
     */
    private $addressPostalCode;

    /**
     * @var string
     */
    private $addressCity;

    /**
     * @var string
     */
    private $addressCountry;

    /**
     * @var float
     */
    private $outstandingAmount;

    /**
     * @var float
     */
    private $outstandingAmountCreated;

    /**
     * @var float
     */
    private $outstandingAmountLate;

    /**
     * @var string
     */
    private $bankAccountIban;

    /**
     * @var string
     */
    private $bankAccountBic;

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

    public function getBankAccountIban(): string
    {
        return $this->bankAccountIban;
    }

    /**
     * @param  string                $bankAccountIban
     * @return MerchantDebtor|static
     */
    public function setBankAccountIban(string $bankAccountIban): MerchantDebtor
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): string
    {
        return $this->bankAccountBic;
    }

    /**
     * @param  string                $bankAccountBic
     * @return MerchantDebtor|static
     */
    public function setBankAccountBic(string $bankAccountBic): MerchantDebtor
    {
        $this->bankAccountBic = $bankAccountBic;

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

            'bank_account_iban' => $this->bankAccountIban,
            'bank_account_bic' => $this->bankAccountBic,
        ]);
    }
}
