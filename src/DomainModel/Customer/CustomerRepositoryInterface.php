<?php

namespace App\DomainModel\Customer;

interface CustomerRepositoryInterface
{
    public function insert(CustomerEntity $customer): void;
    public function getOneByApiKey(string $apiKey):? CustomerEntity;
    public function getOneByApiKeyRaw(string $apiKey):? array;
}
