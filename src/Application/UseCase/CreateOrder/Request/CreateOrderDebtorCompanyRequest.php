<?php

namespace App\Application\UseCase\CreateOrder\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderDebtorCompanyRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $merchantCustomerId;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @Assert\Length(max=255)
     */
    private $taxId;

    /**
     * @Assert\Length(max=255)
     */
    private $taxNumber;

    /**
     * @Assert\Length(max=255)
     */
    private $registrationCourt;

    /**
     * @Assert\Length(max=255)
     */
    private $registrationNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $industrySector;

    /**
     * @Assert\Length(max=255)
     */
    private $subindustrySector;

    /**
     * @Assert\Length(max=255)
     */
    private $employeesNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $legalForm;

    /**
     * @Assert\Type(type="bool")
     */
    private $establishedCustomer;

    /**
     * @Assert\Length(max=255)
     */
    private $addressAddition;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $addressHouseNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $addressStreet;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $addressCity;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^[0-9]{5}$/", match=true)
     */
    private $addressPostalCode;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^[A-Za-z]{2}$/", match=true)
     */
    private $addressCountry;

    public function getMerchantCustomerId(): ?string
    {
        return $this->merchantCustomerId;
    }

    public function setMerchantCustomerId(?string $merchantCustomerId): CreateOrderDebtorCompanyRequest
    {
        $this->merchantCustomerId = $merchantCustomerId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): CreateOrderDebtorCompanyRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): CreateOrderDebtorCompanyRequest
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): CreateOrderDebtorCompanyRequest
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    public function getRegistrationCourt(): ?string
    {
        return $this->registrationCourt;
    }

    public function setRegistrationCourt(?string $registrationCourt): CreateOrderDebtorCompanyRequest
    {
        $this->registrationCourt = $registrationCourt;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): CreateOrderDebtorCompanyRequest
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getIndustrySector(): ?string
    {
        return $this->industrySector;
    }

    public function setIndustrySector(?string $industrySector): CreateOrderDebtorCompanyRequest
    {
        $this->industrySector = $industrySector ? strtoupper($industrySector) : null;

        return $this;
    }

    public function getSubindustrySector(): ?string
    {
        return $this->subindustrySector;
    }

    public function setSubindustrySector(?string $subindustrySector): CreateOrderDebtorCompanyRequest
    {
        $this->subindustrySector = $subindustrySector;

        return $this;
    }

    public function getEmployeesNumber(): ?string
    {
        return $this->employeesNumber;
    }

    public function setEmployeesNumber(?string $employeesNumber): CreateOrderDebtorCompanyRequest
    {
        $this->employeesNumber = $employeesNumber;

        return $this;
    }

    public function getLegalForm(): ?string
    {
        return $this->legalForm;
    }

    public function setLegalForm(?string $legalForm): CreateOrderDebtorCompanyRequest
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function isEstablishedCustomer(): bool
    {
        return $this->establishedCustomer;
    }

    public function setEstablishedCustomer(bool $establishedCustomer): CreateOrderDebtorCompanyRequest
    {
        $this->establishedCustomer = $establishedCustomer;

        return $this;
    }

    public function getAddressAddition(): ?string
    {
        return $this->addressAddition;
    }

    public function setAddressAddition(?string $addressAddition): CreateOrderDebtorCompanyRequest
    {
        $this->addressAddition = $addressAddition;

        return $this;
    }

    public function getAddressHouseNumber(): ?string
    {
        return $this->addressHouseNumber;
    }

    public function setAddressHouseNumber(?string $addressHouseNumber): CreateOrderDebtorCompanyRequest
    {
        $this->addressHouseNumber = $addressHouseNumber;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): CreateOrderDebtorCompanyRequest
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): CreateOrderDebtorCompanyRequest
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): CreateOrderDebtorCompanyRequest
    {
        $this->addressPostalCode = $addressPostalCode ? strtoupper($addressPostalCode) : null;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): CreateOrderDebtorCompanyRequest
    {
        $this->addressCountry = $addressCountry ? strtoupper($addressCountry) : null;

        return $this;
    }
}
