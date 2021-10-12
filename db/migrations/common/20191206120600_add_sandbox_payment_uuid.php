<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantPdoRepository;

class AddSandboxPaymentUuid extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(MerchantPdoRepository::TABLE_NAME)
            ->addColumn('sandbox_payment_merchant_id', 'string', ['null' => true, 'limit' => 36, 'after' => 'payment_merchant_id'])
            ->addIndex('sandbox_payment_merchant_id')
            ->save();
    }
}
