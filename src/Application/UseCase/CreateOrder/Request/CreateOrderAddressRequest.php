<?php

namespace App\Application\UseCase\CreateOrder\Request;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="CreateOrderAddressRequest",
 *     title="Address",
 *     required={
 *          "street", "city", "postal_code", "country"
 *     },
 *     properties={
 *          @OA\Property(property="addition", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(
 *              property="house_number",
 *              ref="#/components/schemas/TinyText",
 *              example="4",
 *              nullable=true,
 *              description="House number. Leave empty if it is provided as part of the street"
 *          ),
 *          @OA\Property(
 *              property="street",
 *              ref="#/components/schemas/TinyText",
 *              example="Charlottenstr. 45",
 *              description="Street can be with house number together or provide house number in the dedicated field."
 *          ),
 *          @OA\Property(property="city", ref="#/components/schemas/TinyText", example="Berlin", description="City, district, suburb, town, or village.", example="Berlin"),
 *          @OA\Property(property="postal_code", ref="#/components/schemas/PostalCode", description="ZIP or postal code.", example="94111"),
 *          @OA\Property(property="country", ref="#/components/schemas/CountryCode", description="Two-letter country code", example="DE"),
 *     }
 * )
 */
class CreateOrderAddressRequest implements ArrayableInterface
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
     * @Assert\Regex(pattern="/^[A-Z]{2}$/", match=true)
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

    public function toArray(): array
    {
        return [
            'addition' => $this->getAddition(),
            'house_number' => $this->getHouseNumber(),
            'street' => $this->getStreet(),
            'city' => $this->getCity(),
            'postal_code' => $this->getPostalCode(),
            'country' => $this->getCountry(),
        ];
    }
}
