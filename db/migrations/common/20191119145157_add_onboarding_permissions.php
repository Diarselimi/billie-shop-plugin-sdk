<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddOnboardingPermissions extends TransactionalMigration
{
    use MigrationHelperTrait;

    protected function migrate()
    {
        $this->execute($this->buildAppendRolePermissionSql(MerchantUserPermissions::VIEW_ONBOARDING, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_SUPPORT['name'],
            MerchantUserDefaultRoles::ROLE_VIEW_ONLY['name'],
        ]));

        $this->execute($this->buildAppendRolePermissionSql(MerchantUserPermissions::MANAGE_ONBOARDING, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
        ]));
    }
}
