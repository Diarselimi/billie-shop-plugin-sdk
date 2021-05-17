<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddInvoiceRolesToAllMerchants extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $this->execute($this->buildAppendRolePermissionSql(MerchantUserPermissions::UPDATE_INVOICES, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_SUPPORT['name'],
        ]));

        $this->execute($this->buildAppendRolePermissionSql(MerchantUserPermissions::VIEW_INVOICES, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_SUPPORT['name'],
            MerchantUserDefaultRoles::ROLE_VIEW_ONLY['name'],
        ]));
    }
}
