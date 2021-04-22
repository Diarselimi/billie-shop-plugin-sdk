<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddCancelInvoiceRoleToAllMerchants extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $role = MerchantUserPermissions::CANCEL_INVOICES;
        $this->buildAppendRolePermissionSql($role, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_SUPPORT['name'],
        ]);
    }
}
