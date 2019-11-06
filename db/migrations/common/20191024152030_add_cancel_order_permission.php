<?php

use App\DomainModel\MerchantUser\MerchantUserPermissions;
use Phinx\Migration\AbstractMigration;

class AddCancelOrderPermission extends AbstractMigration
{
    protected function migrate()
    {
        $role = MerchantUserPermissions::CANCEL_ORDERS;

        // add it to all the write roles
        $this->execute("
            UPDATE merchant_user_roles
            SET permissions = JSON_SET(permissions, '$', '$role')
            WHERE name IN ('admin', 'support')
            AND permissions IS NOT NULL
            AND NOT JSON_CONTAINS(permissions, '\"$role\"')
        ");

        // add it to all the users assigned to write roles but with explicit permissions
        $this->execute("
            UPDATE merchant_users
            INNER JOIN merchant_user_roles ON merchant_users.role_id = merchant_user_roles.id
            SET merchant_users.permissions = JSON_SET(merchant_users.permissions, '$', '$role')
            WHERE name IN ('admin', 'support')
            AND merchant_users.permissions IS NOT NULL
            AND NOT JSON_CONTAINS(merchant_users.permissions, '\"$role\"')
        ");
    }
}
