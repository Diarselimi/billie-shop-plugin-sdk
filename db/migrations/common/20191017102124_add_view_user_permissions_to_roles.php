<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\TransactionalMigration;

/**
 * APIS-1433 + APIS-1482
 */
class AddViewUserPermissionsToRoles extends TransactionalMigration
{
    protected function migrate()
    {
        $sql = "UPDATE merchant_user_roles 
                    SET permissions = JSON_ARRAY_APPEND(permissions, '$', '%s') 
                    WHERE `name` IN(%s)";

        $viewUsersRoles = array_map(function ($roleName) {
            return "'{$roleName}'";
        }, [
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            MerchantUserDefaultRoles::ROLE_SUPPORT['name'],
            MerchantUserDefaultRoles::ROLE_VIEW_ONLY['name'],
        ]);

        $this->execute(sprintf($sql, MerchantUserPermissions::VIEW_USERS, implode(', ', $viewUsersRoles)));
    }
}
