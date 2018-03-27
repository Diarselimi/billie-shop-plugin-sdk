<?php

namespace App\Infrastructure;

use App\DomainModel\Customer\CustomerEntity;
use App\DomainModel\Customer\CustomerRepositoryInterface;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function insert(CustomerEntity $customer): void
    {
    }

    public function getOneByApiKey(string $apiKey): CustomerEntity
    {
    }
}
