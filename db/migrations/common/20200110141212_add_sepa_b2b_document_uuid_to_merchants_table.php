<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantRepository;

class AddSepaB2BDocumentUuidToMerchantsTable extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(MerchantRepository::TABLE_NAME)
            ->addColumn('sepa_b2b_document_uuid', 'string', [
                'null' => true,
                'after' => 'sandbox_payment_merchant_id',
                'limit' => 36,
            ])
            ->save();
    }
}
