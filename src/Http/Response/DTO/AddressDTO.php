<?php

declare(strict_types=1);

namespace App\Http\Response\DTO;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\ArrayableInterface;

class AddressDTO implements ArrayableInterface
{
    private AddressEntity $addressEntity;

    public function __construct(AddressEntity $addressEntity)
    {
        $this->addressEntity = $addressEntity;
    }

    public function toArray(): array
    {
        return [
            'street' => $this->addressEntity->getStreet(),
            'house_number' => $this->addressEntity->getHouseNumber(),
            'postal_code' => $this->addressEntity->getPostalCode(),
            'city' => $this->addressEntity->getCity(),
            'country' => $this->addressEntity->getCountry(),
        ];
    }
}
