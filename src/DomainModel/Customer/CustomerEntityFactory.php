<?php

namespace App\DomainModel\Customer;

class CustomerEntityFactory
{
    public function create(string $name, array $roles)
    {
        return (new CustomerEntity())
            ->setName($name)
            ->setRoles($roles)
            ->setApiKey('test')
        ;
    }
}
