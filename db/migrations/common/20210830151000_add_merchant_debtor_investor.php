<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantDebtorRepository;

final class AddMerchantDebtorInvestor extends TransactionalMigration
{
    public function migrate()
    {
        $this->table(MerchantDebtorRepository::TABLE_NAME)
            ->addColumn('investor_uuid', 'string', [
                'null' => true,
                'length' => 36,
                'after' => 'payment_debtor_id',
            ])->update();
    }
}
