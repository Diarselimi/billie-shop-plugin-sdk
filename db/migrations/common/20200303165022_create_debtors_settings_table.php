<?php

use App\Infrastructure\Phinx\TransactionalMigration;

class CreateDebtorsSettingsTable extends TransactionalMigration
{
    protected function migrate()
    {
        $this
            ->table('debtor_settings')
            ->addColumn('company_uuid', 'char', ['null' => false, 'limit' => 36])
            ->addColumn('is_whitelisted', 'boolean', ['null' => false, 'default' => 0])
            ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addIndex('company_uuid', ['unique' => true])
            ->create()
        ;
    }
}
