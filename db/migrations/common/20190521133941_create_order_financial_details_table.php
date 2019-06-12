<?php

use App\Infrastructure\Repository\OrderFinancialDetailsRepository;
use Phinx\Migration\AbstractMigration;

class CreateOrderFinancialDetailsTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(OrderFinancialDetailsRepository::TABLE_NAME)
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('amount_gross', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('amount_net', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('amount_tax', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('duration', 'integer', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
            ->addIndex('order_id')
            ->create()
        ;
    }
}
