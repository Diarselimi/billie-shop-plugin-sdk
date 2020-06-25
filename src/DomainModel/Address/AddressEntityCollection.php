<?php

declare(strict_types=1);

namespace App\DomainModel\Address;

class AddressEntityCollection
{
    private $addresses;

    public function __construct(AddressEntity ...$addresses)
    {
        $this->addresses = $addresses;
    }

    public function getAddressByUuid(string $uuid): ?AddressEntity
    {
        foreach ($this->addresses as $address) {
            if ($address->getUuid() == $uuid) {
                return $address;
            }
        }

        return null;
    }
}
