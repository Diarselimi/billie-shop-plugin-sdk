<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\OrderFinancialDetailsRepository;

final class AddUnshippedAmountsColumns extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(OrderFinancialDetailsRepository::TABLE_NAME)
            ->addColumn(
                'unshipped_amount_net',
                'decimal',
                ['after' => 'amount_tax', 'null' => true, 'precision' => 12, 'scale' => 2]
            )
            ->addColumn(
                'unshipped_amount_gross',
                'decimal',
                ['after' => 'unshipped_amount_net', 'null' => true, 'precision' => 12, 'scale' => 2]
            )
            ->addColumn(
                'unshipped_amount_tax',
                'decimal',
                ['after' => 'unshipped_amount_gross', 'null' => true, 'precision' => 12, 'scale' => 2]
            )
            ->save();
    }
}
