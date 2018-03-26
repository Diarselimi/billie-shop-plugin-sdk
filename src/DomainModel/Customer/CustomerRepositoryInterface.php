<?php

namespace App\DomainModel\Customer;

interface CustomerRepositoryInterface
{
    public function insert(CustomerEntity $customerEntity): void;
    public function getOneByApiKey(string $apiKey): CustomerEntity;
}
