<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\OrderPdoRepository;

final class AddOrderMandateUuid extends TransactionalMigration
{
    public function migrate()
    {
        $this->table(OrderPdoRepository::TABLE_NAME)
            ->addColumn('debtor_sepa_mandate_uuid', 'string', [
                'null' => true,
                'length' => 36,
                'after' => 'duration_extension',
            ])->update();
    }
}
