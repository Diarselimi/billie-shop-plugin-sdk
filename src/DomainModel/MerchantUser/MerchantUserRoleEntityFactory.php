<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserRoleEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantUserRoleEntity
    {
        $permissions = array_unique((array) json_decode($row['permissions'] ?? '[]', true));
        sort($permissions);

        return (new MerchantUserRoleEntity())
            ->setId($row['id'])
            ->setUuid($row['uuid'])
            ->setMerchantId($row['merchant_id'])
            ->setName($row['name'])
            ->setPermissions($permissions)
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']));
    }

    public function create(int $merchantId, string $uuid, string $name, array $permissions): MerchantUserRoleEntity
    {
        return (new MerchantUserRoleEntity())
            ->setName($name)
            ->setUuid($uuid)
            ->setMerchantId($merchantId)
            ->setPermissions($permissions);
    }
}
