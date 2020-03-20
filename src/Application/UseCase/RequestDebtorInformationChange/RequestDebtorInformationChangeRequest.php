<?php

namespace App\Application\UseCase\RequestDebtorInformationChange;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RequestDebtorInformationChangeRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Uuid()
     */
    private $debtorUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $merchantUserId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $city;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $postalCode;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $street;

    /**
     * @Assert\Type(type="string")
     */
    private $houseNumber;

    /**
     * @Assert\IsTrue()
     */
    private $tcAccepted;

    public function getDebtorUuid(): string
    {
        return $this->debtorUuid;
    }

    public function setDebtorUuid(string $debtorUuid): RequestDebtorInformationChangeRequest
    {
        $this->debtorUuid = $debtorUuid;

        return $this;
    }

    public function getMerchantUserId(): int
    {
        return $this->merchantUserId;
    }

    public function setMerchantUserId(int $merchantUserId): RequestDebtorInformationChangeRequest
    {
        $this->merchantUserId = $merchantUserId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): RequestDebtorInformationChangeRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(?string $city): RequestDebtorInformationChangeRequest
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): RequestDebtorInformationChangeRequest
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(?string $street): RequestDebtorInformationChangeRequest
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): RequestDebtorInformationChangeRequest
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function isTcAccepted(): bool
    {
        return $this->tcAccepted;
    }

    public function setTcAccepted(?bool $tcAccepted): RequestDebtorInformationChangeRequest
    {
        $this->tcAccepted = $tcAccepted;

        return $this;
    }
}
