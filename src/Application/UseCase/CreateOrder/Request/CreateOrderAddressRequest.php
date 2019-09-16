<?php

namespace App\Application\UseCase\CreateOrder\Request;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateOrderAddressRequest",
 *     title="Address",
 *     required={
 *          "street", "city", "postal_code", "country"
 *     },
 *     properties={
 *          @OA\Property(property="addition", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="house_number", ref="#/components/schemas/TinyText", example="4", nullable=true),
 *          @OA\Property(property="street", ref="#/components/schemas/TinyText", example="Charlotten Str."),
 *          @OA\Property(property="city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="postal_code", ref="#/components/schemas/PostalCode"),
 *          @OA\Property(property="country", ref="#/components/schemas/CountryCode"),
 *     }
 * )
 */
class CreateOrderAddressRequest
{
    /**
     * @Assert\Length(max=255)
     */
    private $addition;

    /**
     * @Assert\Length(max=255)
     */
    private $houseNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $street;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $city;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    private $postalCode;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^[A-Za-z]{2}$/", match=true)
     */
    private $country;

    public function getAddition(): ?string
    {
        return $this->addition;
    }

    public function setAddition(?string $addition): CreateOrderAddressRequest
    {
        $this->addition = $addition;

        return $this;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): CreateOrderAddressRequest
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): CreateOrderAddressRequest
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): CreateOrderAddressRequest
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): CreateOrderAddressRequest
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): CreateOrderAddressRequest
    {
        $this->country = $country;

        return $this;
    }
}
