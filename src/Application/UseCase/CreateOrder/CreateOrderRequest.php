<?php

namespace App\Application\UseCase\CreateOrder;

class CreateOrderRequest
{
    private $customerId;
    private $amountNet;
    private $amountGross;
    private $amountTax;
    private $comment;
    private $duration;
    private $externalCode;

    private $deliveryAddressAddition;
    private $deliveryAddressHouseNumber;
    private $deliveryAddressStreet;
    private $deliveryAddressCity;
    private $deliveryAddressPostalCode;
    private $deliveryAddressCountry;

    private $merchantCustomerId;

    private $debtorCompanyName;
    private $debtorCompanyTaxId;
    private $debtorCompanyTaxNumber;
    private $debtorCompanyRegistrationCourt;
    private $debtorCompanyRegistrationNumber;
    private $debtorCompanyIndustrySector;
    private $debtorCompanySubindustrySector;
    private $debtorCompanyEmployeesNumber;
    private $debtorCompanyLegalForm;
    private $debtorCompanyEstablishedCustomer;

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

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getAmountNet()
    {
        return $this->amountNet;
    }

    public function setAmountNet($amountNet)
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getAmountGross()
    {
        return $this->amountGross;
    }

    public function setAmountGross($amountGross)
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getAmountTax()
    {
        return $this->amountTax;
    }

    public function setAmountTax($amountTax)
    {
        $this->amountTax = $amountTax;

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

    public function getDeliveryAddressAddition()
    {
        return $this->deliveryAddressAddition;
    }

    public function setDeliveryAddressAddition($deliveryAddressAddition)
    {
        $this->deliveryAddressAddition = $deliveryAddressAddition;

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

    public function getMerchantCustomerId()
    {
        return $this->merchantCustomerId;
    }

    public function setMerchantCustomerId($merchantCustomerId)
    {
        $this->merchantCustomerId = $merchantCustomerId;

        return $this;
    }

    public function getDebtorCompanyName()
    {
        return $this->debtorCompanyName;
    }

    public function setDebtorCompanyName($debtorCompanyName)
    {
        $this->debtorCompanyName = $debtorCompanyName;

        return $this;
    }

    public function getDebtorCompanyTaxId()
    {
        return $this->debtorCompanyTaxId;
    }

    public function setDebtorCompanyTaxId($debtorCompanyTaxId)
    {
        $this->debtorCompanyTaxId = $debtorCompanyTaxId;

        return $this;
    }

    public function getDebtorCompanyTaxNumber()
    {
        return $this->debtorCompanyTaxNumber;
    }

    public function setDebtorCompanyTaxNumber($debtorCompanyTaxNumber)
    {
        $this->debtorCompanyTaxNumber = $debtorCompanyTaxNumber;

        return $this;
    }

    public function getDebtorCompanyRegistrationCourt()
    {
        return $this->debtorCompanyRegistrationCourt;
    }

    public function setDebtorCompanyRegistrationCourt($debtorCompanyRegistrationCourt)
    {
        $this->debtorCompanyRegistrationCourt = $debtorCompanyRegistrationCourt;

        return $this;
    }

    public function getDebtorCompanyRegistrationNumber()
    {
        return $this->debtorCompanyRegistrationNumber;
    }

    public function setDebtorCompanyRegistrationNumber($debtorCompanyRegistrationNumber)
    {
        $this->debtorCompanyRegistrationNumber = $debtorCompanyRegistrationNumber;

        return $this;
    }

    public function getDebtorCompanyIndustrySector()
    {
        return $this->debtorCompanyIndustrySector;
    }

    public function setDebtorCompanyIndustrySector($debtorCompanyIndustrySector)
    {
        $this->debtorCompanyIndustrySector = $debtorCompanyIndustrySector;

        return $this;
    }

    public function getDebtorCompanySubindustrySector()
    {
        return $this->debtorCompanySubindustrySector;
    }

    public function setDebtorCompanySubindustrySector($debtorCompanySubindustrySector)
    {
        $this->debtorCompanySubindustrySector = $debtorCompanySubindustrySector;

        return $this;
    }

    public function getDebtorCompanyEmployeesNumber()
    {
        return $this->debtorCompanyEmployeesNumber;
    }

    public function setDebtorCompanyEmployeesNumber($debtorCompanyEmployeesNumber)
    {
        $this->debtorCompanyEmployeesNumber = $debtorCompanyEmployeesNumber;

        return $this;
    }

    public function getDebtorCompanyLegalForm()
    {
        return $this->debtorCompanyLegalForm;
    }

    public function setDebtorCompanyLegalForm($debtorCompanyLegalForm)
    {
        $this->debtorCompanyLegalForm = $debtorCompanyLegalForm;

        return $this;
    }

    public function getDebtorCompanyEstablishedCustomer()
    {
        return $this->debtorCompanyEstablishedCustomer;
    }

    public function setDebtorCompanyEstablishedCustomer($debtorCompanyEstablishedCustomer)
    {
        $this->debtorCompanyEstablishedCustomer = $debtorCompanyEstablishedCustomer;

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
