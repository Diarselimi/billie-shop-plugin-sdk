<?php

use App\Infrastructure\Repository\MerchantUserRepository;
use Phinx\Migration\AbstractMigration;

class AddMerchantUsersTable extends AbstractMigration
{
    public function up()
    {
        $this
            ->table(MerchantUserRepository::TABLE_NAME)
            ->addColumn('user_id', 'string', ['null' => false, 'limit' => 36])
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('roles', 'json', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex('user_id', ['unique' => true])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->create()
        ;
    }
}
