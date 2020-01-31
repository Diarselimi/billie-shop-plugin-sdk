<?php

namespace App\Application\UseCase\CheckoutSessionConfirmOrder;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DebtorCompanyRequest",
 *     title="Debtor Company Request",
 *     required={"name", "address_street", "address_city", "address_postal_code", "address_country"},
 *     properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie GmbH"),
 *          @OA\Property(property="address_addition", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", example="4", nullable=true),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", example="Charlottenstr."),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="address_postal_code", ref="#/components/schemas/PostalCode", example="10969"),
 *          @OA\Property(property="address_country", ref="#/components/schemas/CountryCode", example="DE")
 *     }
 * )
 */
class DebtorCompanyRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @Assert\Length(max=255)
     */
    private $addressAddition;

    /**
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): DebtorCompanyRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressAddition(): ?string
    {
        return $this->addressAddition;
    }

    public function setAddressAddition($addressAddition): DebtorCompanyRequest
    {
        $this->addressAddition = $addressAddition;

        return $this;
    }

    public function getAddressHouseNumber(): ?string
    {
        return $this->addressHouseNumber;
    }

    public function setAddressHouseNumber($addressHouseNumber): DebtorCompanyRequest
    {
        $this->addressHouseNumber = $addressHouseNumber;

        return $this;
    }

    public function getAddressStreet(): string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet($addressStreet): DebtorCompanyRequest
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): string
    {
        return $this->addressCity;
    }

    public function setAddressCity($addressCity): DebtorCompanyRequest
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostalCode(): string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode($addressPostalCode): DebtorCompanyRequest
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCountry(): string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry($addressCountry): DebtorCompanyRequest
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }
}
