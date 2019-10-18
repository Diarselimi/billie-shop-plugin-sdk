<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRepository;

class AddRoleIdToMerchantUsers extends TransactionalMigration
{
    protected function migrate()
    {
        $this
            ->table(MerchantUserRepository::TABLE_NAME)
            ->addColumn('role_id', 'integer', ['null' => true, 'after' => 'last_name'])
            ->save();
    }
}
