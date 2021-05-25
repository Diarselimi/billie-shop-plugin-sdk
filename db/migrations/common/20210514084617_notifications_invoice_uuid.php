<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;

final class NotificationsInvoiceUuid extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table('order_notifications')->addColumn(
            'invoice_uuid',
            'char',
            [
                'length' => 36,
                'null' => true,
                'after' => 'order_id',
            ]
        )->save();
    }
}
