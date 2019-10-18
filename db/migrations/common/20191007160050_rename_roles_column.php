<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRepository;

class RenameRolesColumn extends TransactionalMigration
{
    protected function migrate()
    {
        $this
            ->table(MerchantUserRepository::TABLE_NAME)
            ->renameColumn('roles', 'permissions')
            ->changeColumn('permissions', 'json', ['null' => true])
            ->save();
    }
}
