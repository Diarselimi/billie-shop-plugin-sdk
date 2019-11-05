<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRoleRepository;

class AddBillieAdminRole extends TransactionalMigration
{
    protected function migrate()
    {
        $now = date('Y-m-d H:i:s');
        $sql = "
            INSERT INTO %s (id, uuid, merchant_id, name, permissions, created_at, updated_at)
            SELECT NULL, UUID(), id, '%s', '%s', '%s', '%s'
            FROM merchants
            ORDER BY id ASC
            ON DUPLICATE KEY UPDATE updated_at = '%s'
        ";

        $sql = sprintf(
            $sql,
            MerchantUserRoleRepository::TABLE_NAME,
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            json_encode(MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['permissions']),
            $now,
            $now,
            $now
        );

        $this->execute($sql);
    }
}
