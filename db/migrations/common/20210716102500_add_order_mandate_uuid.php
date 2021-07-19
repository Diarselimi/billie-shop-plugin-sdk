<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\OrderRepository;

final class AddOrderMandateUuid extends TransactionalMigration
{
    public function migrate()
    {
        $this->table(OrderRepository::TABLE_NAME)
            ->addColumn('debtor_sepa_mandate_uuid', 'string', [
                'null' => true,
                'length' => 36,
                'after' => 'duration_extension',
            ])->update();
    }
}
