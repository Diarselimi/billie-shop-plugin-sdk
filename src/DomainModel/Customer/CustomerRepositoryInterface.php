<?php

namespace App\DomainModel\Customer;

interface CustomerRepositoryInterface
{
    public function insert(CustomerEntity $customer): void;
    public function getOneByApiKeyRaw(string $apiKey):? array;
    public function delete(CustomerEntity $customer): void;
}
