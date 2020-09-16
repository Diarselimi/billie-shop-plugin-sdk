<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddTestDataPermissions extends TransactionalMigration
{
    use MigrationHelperTrait;

    protected function migrate()
    {
        $this->execute($this->buildAppendRolePermissionSql(MerchantUserPermissions::ACCESS_TEST_DATA, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_DEVELOPER['name'],
        ]));
    }
}
