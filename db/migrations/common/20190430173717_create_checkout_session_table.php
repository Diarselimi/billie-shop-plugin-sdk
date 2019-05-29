<?php

use App\Infrastructure\Repository\CheckoutSessionRepository;
use Phinx\Migration\AbstractMigration;

class CreateCheckoutSessionTable extends AbstractMigration
{
    public function change()
    {
        $this->table(CheckoutSessionRepository::TABLE_NAME)
            ->addColumn('uuid', 'uuid', ['limit' => '36', 'null' => false])
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('merchant_debtor_external_id', 'string', ['null' => false])
            ->addColumn('is_active', 'boolean', ['default' => 1])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['uuid'], ['unique' => true])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->create();
    }
}
