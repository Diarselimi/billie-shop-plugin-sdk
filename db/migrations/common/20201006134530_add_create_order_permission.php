<?php

use App\DomainModel\MerchantUser\MerchantUserPermissions;
use Phinx\Migration\AbstractMigration;

class AddCreateOrderPermission extends AbstractMigration
{
    public function up()
    {
        $permission = MerchantUserPermissions::CREATE_ORDERS;

        $this->execute("
            UPDATE merchant_user_roles
            SET permissions = JSON_ARRAY_APPEND(permissions, '$', '$permission')
            WHERE name = 'support'
            AND permissions IS NOT NULL
            AND NOT JSON_CONTAINS(permissions, '\"$permission\"')
        ");
    }
}
