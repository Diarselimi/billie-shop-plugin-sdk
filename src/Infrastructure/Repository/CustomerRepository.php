<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Customer\CustomerEntity;
use App\DomainModel\Customer\CustomerRepositoryInterface;
use App\DomainModel\Exception\RepositoryException;

class CustomerRepository extends AbstractRepository implements CustomerRepositoryInterface
{
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

    public function getOneByApiKeyRaw(string $apiKey):? array
    {
        $customer = $this->doFetch('
          SELECT id, name, api_key, roles, is_active, available_financing_limit, created_at, updated_at 
          FROM customers 
          WHERE api_key = :api_key
        ', ['api_key' => $apiKey]);

        return $customer ?: null;
    }

    public function delete(CustomerEntity $customer): void
    {
        if (!$this->deleteAllowed) {
            throw new RepositoryException('Delete operation not allowed');
        }

        $stmt = $this->conn->prepare(' DELETE FROM customers WHERE id = :customer');
        $res = $stmt->execute([
            'customer' => $customer->getId(),
        ]);

        if (!$res) {
            throw new RepositoryException('Delete operation failed');
        };
    }
}
