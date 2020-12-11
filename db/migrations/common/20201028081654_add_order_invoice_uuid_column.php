<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\LegacyOrderInvoiceRepository;

final class AddOrderInvoiceUuidColumn extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(LegacyOrderInvoiceRepository::TABLE_NAME)
            ->addColumn('invoice_uuid', 'string', [
                'null' => true,
                'limit' => 36,
                'after' => 'order_id',
            ])
            ->save();
    }
}
