<?php

use App\Infrastructure\Phinx\TransactionalMigration;

final class UpdateMerchantExternalIdColumn extends TransactionalMigration
{
    public function migrate(): void
    {
        $this->table('debtor_external_data')
            ->changeColumn('merchant_external_id', 'string', ['null' => true])
            ->save();

        $this->table('checkout_sessions')
            ->changeColumn('merchant_debtor_external_id', 'string', ['null' => true])
            ->save();
    }
}
