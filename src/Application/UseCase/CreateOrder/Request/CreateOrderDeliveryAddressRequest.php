<?php

namespace App\Application\UseCase\CreateOrder\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderDeliveryAddressRequest
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
     * @Assert\Regex(pattern="/^[0-9]{5}$/", match=true)
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

    public function setAddition(?string $addition): CreateOrderDeliveryAddressRequest
    {
        $this->addition = $addition;

        return $this;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): CreateOrderDeliveryAddressRequest
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): CreateOrderDeliveryAddressRequest
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): CreateOrderDeliveryAddressRequest
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): CreateOrderDeliveryAddressRequest
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): CreateOrderDeliveryAddressRequest
    {
        $this->country = $country;

        return $this;
    }
}
