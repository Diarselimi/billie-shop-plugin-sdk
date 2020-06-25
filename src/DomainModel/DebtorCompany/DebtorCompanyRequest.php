<?php

namespace App\DomainModel\DebtorCompany;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

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
     * No need for validation for now
     */
    private $addressRequest;

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

    public function getAddressRequest(): CreateOrderAddressRequest
    {
        return $this->addressRequest;
    }

    public function setAddressRequest(CreateOrderAddressRequest $addressRequest): DebtorCompanyRequest
    {
        //support old format
        $this->addressAddition = $addressRequest->getAddition();
        $this->addressStreet = $addressRequest->getStreet();
        $this->addressHouseNumber = $addressRequest->getHouseNumber();
        $this->addressPostalCode = $addressRequest->getPostalCode();
        $this->addressCity = $addressRequest->getCity();
        $this->addressCountry = $addressRequest->getCountry();

        $this->addressRequest = $addressRequest;

        return $this;
    }
}
