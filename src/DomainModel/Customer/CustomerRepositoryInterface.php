<?php

namespace App\DomainModel\Customer;

interface CustomerRepositoryInterface
{
    public function insert(CustomerEntity $customer): void;
    public function getOneById(int $id): ?CustomerEntity;
    public function getOneByApiKeyRaw(string $apiKey): ?array;
}
