<?php

use App\DomainModel\MerchantUser\MerchantUserEntity;
use Phinx\Migration\AbstractMigration;

class AssignDefaultRolesToMerchantUsers extends AbstractMigration
{
    public function change()
    {
        $defaultRoles = json_encode(MerchantUserEntity::DEFAULT_ROLES);

        $this->execute(
            "UPDATE merchant_users SET roles = '$defaultRoles'"
        );
    }
}
