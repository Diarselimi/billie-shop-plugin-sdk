<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserEntityFactory
{
    private $roleEntityFactory;

    public function __construct(MerchantUserRoleEntityFactory $roleEntityFactory)
    {
        $this->roleEntityFactory = $roleEntityFactory;
    }

    public function createFromDatabaseRow(array $row): MerchantUserEntity
    {
        return (new MerchantUserEntity())
            ->setId($row['id'])
            ->setUuid($row['user_id'])
            ->setMerchantId($row['merchant_id'])
            ->setSignatoryPowerUuid($row['signatory_power_uuid'])
            ->setRoleId($row['role_id'])
            ->setFirstName($row['first_name'])
            ->setLastName($row['last_name'])
            ->setPermissions($this->roleEntityFactory->decodePermissions($row))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']));
    }

    public function create(int $merchantId, int $roleId, string $firstName, string $lastName, array $permissions = [], ?string $uuid = null): MerchantUserEntity
    {
        $user = (new MerchantUserEntity())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setMerchantId($merchantId)
            ->setRoleId($roleId)
            ->setPermissions($permissions);

        if ($uuid !== null) {
            $user->setUuid($uuid);
        }

        return $user;
    }
}
