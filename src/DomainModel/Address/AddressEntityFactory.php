<?php

namespace App\DomainModel\Address;

class AddressEntityFactory
{
    public function create(string $name, array $roles)
    {
        return (new AddressEntity())
            ->setCity('test')
        ;
    }
}
