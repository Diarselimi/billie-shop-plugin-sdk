<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantUserEntity
    {
        return (new MerchantUserEntity())
            ->setId($row['id'])
            ->setUserId($row['user_id'])
            ->setMerchantId($row['merchant_id'])
            ->setRoles(json_decode($row['roles'], true))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
