<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Customer\CustomerEntity;
use App\DomainModel\Customer\CustomerRepositoryInterface;

class CustomerRepository extends AbstractRepository implements CustomerRepositoryInterface
{
    public function insert(CustomerEntity $customer): void
    {
    }

    public function getOneByApiKey(string $apiKey):? CustomerEntity
    {
    }

    public function getOneByApiKeyRaw(string $apiKey):? array
    {
        $customer = $this->fetch('
          SELECT id, name, api_key, roles, is_active, maximal_financing_limit, current_financing_limit, created_at, updated_at 
          FROM customers WHERE 
          api_key = :api_key
        ', ['api_key' => $apiKey]);

        return $customer ?: null;
    }
}
