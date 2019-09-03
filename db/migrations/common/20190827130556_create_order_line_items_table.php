<?php

use App\Infrastructure\Repository\OrderLineItemRepository;
use Phinx\Migration\AbstractMigration;

class CreateOrderLineItemsTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(OrderLineItemRepository::TABLE_NAME)
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('external_id', 'string', ['null' => false])
            ->addColumn('title', 'string', ['null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('quantity', 'integer', ['null' => false])
            ->addColumn('category', 'string', ['null' => true])
            ->addColumn('brand', 'string', ['null' => true])
            ->addColumn('gtin', 'string', ['null' => true])
            ->addColumn('mpn', 'string', ['null' => true])
            ->addColumn('amount_gross', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('amount_tax', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('amount_net', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
            ->addIndex('order_id')
            ->create()
        ;
    }
}
