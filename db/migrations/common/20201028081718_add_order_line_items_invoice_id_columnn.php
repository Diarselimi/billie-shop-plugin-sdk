<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\LegacyOrderInvoiceRepository;
use App\Infrastructure\Repository\OrderLineItemRepository;

final class AddOrderLineItemsInvoiceIdColumnn extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(OrderLineItemRepository::TABLE_NAME)
            ->addColumn('order_invoice_id', 'integer', [
                'null' => true,
                'after' => 'order_id',
            ])
            ->addForeignKey('order_invoice_id', LegacyOrderInvoiceRepository::TABLE_NAME, 'id')
            ->save();
    }
}
