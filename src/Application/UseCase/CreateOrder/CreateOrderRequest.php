<?php

namespace App\Application\UseCase\CreateOrder;

class CreateOrderRequest
{
    private $amount;
    private $comment;
    private $duration;
    private $externalCode;

    private $deliveryAddressHouseNumber;
    private $deliveryAddressStreet;
    private $deliveryAddressCity;
    private $deliveryAddressPostalCode;
    private $deliveryAddressCountry;

    private $debtorCompanyAddressAddition;
    private $debtorCompanyAddressHouseNumber;
    private $debtorCompanyAddressStreet;
    private $debtorCompanyAddressCity;
    private $debtorCompanyAddressPostalCode;
    private $debtorCompanyAddressCountry;

    private $debtorPersonGender;
    private $debtorPersonFirstName;
    private $debtorPersonLastName;
    private $debtorPersonPhoneNumber;
    private $debtorPersonEmail;

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode()
    {
        return $this->externalCode;
    }

    public function setExternalCode($externalCode)
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getDeliveryAddressHouseNumber()
    {
        return $this->deliveryAddressHouseNumber;
    }

    public function setDeliveryAddressHouseNumber($deliveryAddressHouseNumber)
    {
        $this->deliveryAddressHouseNumber = $deliveryAddressHouseNumber;

        return $this;
    }

    public function getDeliveryAddressStreet()
    {
        return $this->deliveryAddressStreet;
    }

    public function setDeliveryAddressStreet($deliveryAddressStreet)
    {
        $this->deliveryAddressStreet = $deliveryAddressStreet;

        return $this;
    }

    public function getDeliveryAddressCity()
    {
        return $this->deliveryAddressCity;
    }

    public function setDeliveryAddressCity($deliveryAddressCity)
    {
        $this->deliveryAddressCity = $deliveryAddressCity;

        return $this;
    }

    public function getDeliveryAddressPostalCode()
    {
        return $this->deliveryAddressPostalCode;
    }

    public function setDeliveryAddressPostalCode($deliveryAddressPostalCode)
    {
        $this->deliveryAddressPostalCode = $deliveryAddressPostalCode;

        return $this;
    }

    public function getDeliveryAddressCountry()
    {
        return $this->deliveryAddressCountry;
    }

    public function setDeliveryAddressCountry($deliveryAddressCountry)
    {
        $this->deliveryAddressCountry = $deliveryAddressCountry;

        return $this;
    }

    public function getDebtorCompanyAddressAddition()
    {
        return $this->debtorCompanyAddressAddition;
    }

    public function setDebtorCompanyAddressAddition($debtorCompanyAddressAddition)
    {
        $this->debtorCompanyAddressAddition = $debtorCompanyAddressAddition;

        return $this;
    }

    public function getDebtorCompanyAddressHouseNumber()
    {
        return $this->debtorCompanyAddressHouseNumber;
    }

    public function setDebtorCompanyAddressHouseNumber($debtorCompanyAddressHouseNumber)
    {
        $this->debtorCompanyAddressHouseNumber = $debtorCompanyAddressHouseNumber;

        return $this;
    }

    public function getDebtorCompanyAddressStreet()
    {
        return $this->debtorCompanyAddressStreet;
    }

    public function setDebtorCompanyAddressStreet($debtorCompanyAddressStreet)
    {
        $this->debtorCompanyAddressStreet = $debtorCompanyAddressStreet;

        return $this;
    }

    public function getDebtorCompanyAddressCity()
    {
        return $this->debtorCompanyAddressCity;
    }

    public function setDebtorCompanyAddressCity($debtorCompanyAddressCity)
    {
        $this->debtorCompanyAddressCity = $debtorCompanyAddressCity;

        return $this;
    }

    public function getDebtorCompanyAddressPostalCode()
    {
        return $this->debtorCompanyAddressPostalCode;
    }

    public function setDebtorCompanyAddressPostalCode($debtorCompanyAddressPostalCode)
    {
        $this->debtorCompanyAddressPostalCode = $debtorCompanyAddressPostalCode;

        return $this;
    }

    public function getDebtorCompanyAddressCountry()
    {
        return $this->debtorCompanyAddressCountry;
    }

    public function setDebtorCompanyAddressCountry($debtorCompanyAddressCountry)
    {
        $this->debtorCompanyAddressCountry = $debtorCompanyAddressCountry;

        return $this;
    }

    public function getDebtorPersonGender()
    {
        return $this->debtorPersonGender;
    }

    public function setDebtorPersonGender($debtorPersonGender)
    {
        $this->debtorPersonGender = $debtorPersonGender;

        return $this;
    }

    public function getDebtorPersonFirstName()
    {
        return $this->debtorPersonFirstName;
    }

    public function setDebtorPersonFirstName($debtorPersonFirstName)
    {
        $this->debtorPersonFirstName = $debtorPersonFirstName;

        return $this;
    }

    public function getDebtorPersonLastName()
    {
        return $this->debtorPersonLastName;
    }

    public function setDebtorPersonLastName($debtorPersonLastName)
    {
        $this->debtorPersonLastName = $debtorPersonLastName;

        return $this;
    }

    public function getDebtorPersonPhoneNumber()
    {
        return $this->debtorPersonPhoneNumber;
    }

    public function setDebtorPersonPhoneNumber($debtorPersonPhoneNumber)
    {
        $this->debtorPersonPhoneNumber = $debtorPersonPhoneNumber;

        return $this;
    }

    public function getDebtorPersonEmail()
    {
        return $this->debtorPersonEmail;
    }

    public function setDebtorPersonEmail($debtorPersonEmail)
    {
        $this->debtorPersonEmail = $debtorPersonEmail;

        return $this;
    }
}
