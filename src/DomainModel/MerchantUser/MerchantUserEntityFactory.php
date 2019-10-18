<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantUserEntity
    {
        $permissions = array_unique((array) json_decode($row['permissions'] ?? '[]', true));
        sort($permissions);

        return (new MerchantUserEntity())
            ->setId($row['id'])
            ->setUserId($row['user_id'])
            ->setMerchantId($row['merchant_id'])
            ->setRoleId($row['role_id'])
            ->setFirstName($row['first_name'])
            ->setLastName($row['last_name'])
            ->setPermissions($permissions)
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function create(int $merchantId, int $roleId, string $userId, string $firstName, string $lastName, array $permissions): MerchantUserEntity
    {
        return (new MerchantUserEntity())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setUserId($userId)
            ->setMerchantId($merchantId)
            ->setRoleId($roleId)
            ->setPermissions($permissions)
        ;
    }
}
