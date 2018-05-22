<?php

namespace App\DomainModel\Merchant;

class MerchantEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantEntity
    {
        return (new MerchantEntity())
            ->setId($row['id'])
            ->setName($row['name'])
            ->setApiKey($row['api_key'])
            ->setAvailableFinancingLimit($row['available_financing_limit'])
            ->setCompanyId($row['company_id'])
            ->setRoles($row['roles'])
            ->setIsActive((bool) $row['is_active'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
