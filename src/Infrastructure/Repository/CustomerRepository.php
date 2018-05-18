<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Customer\CustomerEntity;
use App\DomainModel\Customer\CustomerEntityFactory;
use App\DomainModel\Customer\CustomerRepositoryInterface;

class CustomerRepository extends AbstractRepository implements CustomerRepositoryInterface
{
    private $factory;

    public function __construct(CustomerEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(CustomerEntity $customer): void
    {
        $id = $this->doInsert('
            INSERT INTO customers 
            (name, api_key, roles, is_active, available_financing_limit, created_at, updated_at)
            VALUES
            (:name, :api_key, :roles, :is_active, :available_financing_limit, :created_at, :updated_at)
            
        ', [
            'name' => $customer->getName(),
            'api_key' => $customer->getApiKey(),
            'roles' => $customer->getRoles(),
            'is_active' => $customer->isActive(),
            'available_financing_limit' => $customer->getAvailableFinancingLimit(),
            'created_at' => $customer->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $customer->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $customer->setId($id);
    }

    public function update(CustomerEntity $customer): void
    {
        $customer->setUpdatedAt(new \DateTime());
        $this->doUpdate('
            UPDATE customers 
            SET available_financing_limit = :available_financing_limit, updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $customer->getId(),
            'available_financing_limit' => $customer->getAvailableFinancingLimit(),
            'updated_at' => $customer->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function getOneById(int $id): ?CustomerEntity
    {
        $row = $this->doFetch('
          SELECT id, name, api_key, debtor_id, roles, is_active, available_financing_limit, created_at, updated_at 
          FROM customers 
          WHERE id = :id
        ', ['id' => $id]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByApiKeyRaw(string $apiKey):? array
    {
        $customer = $this->doFetch('
          SELECT id, name, api_key, debtor_id, roles, is_active, available_financing_limit, created_at, updated_at 
          FROM customers 
          WHERE api_key = :api_key
        ', ['api_key' => $apiKey]);

        return $customer ?: null;
    }
}
