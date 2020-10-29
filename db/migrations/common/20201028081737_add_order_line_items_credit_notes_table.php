<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\OrderLineItemCreditNoteRepository;
use App\Infrastructure\Repository\OrderLineItemRepository;

final class AddOrderLineItemsCreditNotesTable extends TransactionalMigration
{
    protected function migrate()
    {
        $this
            ->table(OrderLineItemCreditNoteRepository::TABLE_NAME)
            ->addColumn('credit_note_uuid', 'string', ['null' => false, 'limit' => 36])
            ->addColumn('order_line_item_id', 'integer', ['null' => false])
            ->addForeignKey('order_line_item_id', OrderLineItemRepository::TABLE_NAME, 'id')
            ->create()
        ;
    }
}
