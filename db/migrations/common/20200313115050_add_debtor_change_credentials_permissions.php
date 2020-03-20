<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddDebtorChangeCredentialsPermissions extends TransactionalMigration
{
    use MigrationHelperTrait;

    protected function migrate()
    {
        $this->execute($this->buildAppendRolePermissionSql(MerchantUserPermissions::CHANGE_DEBTOR_INFORMATION, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_SUPPORT['name'],
        ]));
    }
}
