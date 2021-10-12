<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantPdoRepository;

final class AddMerchantExternalIdentifier extends TransactionalMigration
{
    public function migrate()
    {
        $this->table(MerchantPdoRepository::TABLE_NAME)
            ->addColumn('klarna_identifier', 'string', [
                'null' => true,
                'length' => 36,
                'after' => 'payment_merchant_id',
            ])->update();
    }
}
