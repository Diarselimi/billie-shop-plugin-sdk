<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\TransactionalMigration;

/**
 * APIS-1433 + APIS-1482
 */
class AddManageUserPermissionsToRoles extends TransactionalMigration
{
    protected function migrate()
    {
        $sql = "UPDATE merchant_user_roles 
                    SET permissions = JSON_ARRAY_APPEND(permissions, '$', '%s') 
                    WHERE `name` IN (%s)";

        $manageUsersRoles = array_map(function ($roleName) {
            return "'{$roleName}'";
        }, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
        ]);

        $this->execute(sprintf($sql, MerchantUserPermissions::MANAGE_USERS, implode(', ', $manageUsersRoles)));
    }
}
