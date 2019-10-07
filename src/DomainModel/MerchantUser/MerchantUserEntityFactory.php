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
            ->setFirstName($row['first_name'])
            ->setLastName($row['last_name'])
            ->setRoles(json_decode($row['roles'], true))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function create(int $merchantId, string $userId, string $firstName, string $lastName, array $roles): MerchantUserEntity
    {
        return (new MerchantUserEntity())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setUserId($userId)
            ->setMerchantId($merchantId)
            ->setRoles($roles)
        ;
    }
}
