<?php

namespace App\DomainModel\Customer;

class CustomerEntityFactory
{
    public function createFromDatabaseRow(array $row): CustomerEntity
    {
        return (new CustomerEntity())
            ->setName($row['name'])
            ->setApiKey($row['api_key'])
            ->setAvailableFinancingLimit($row['available_financing_limit'])
            ->setDebtorId($row['debtor_id'])
            ->setRoles($row['roles'])
            ->setIsActive((bool) $row['is_active'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
