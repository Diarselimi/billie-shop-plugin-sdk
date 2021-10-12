<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantPdoRepository;

class AddSepaB2BDocumentUuidToMerchantsTable extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(MerchantPdoRepository::TABLE_NAME)
            ->addColumn('sepa_b2b_document_uuid', 'string', [
                'null' => true,
                'after' => 'sandbox_payment_merchant_id',
                'limit' => 36,
            ])
            ->save();
    }
}
