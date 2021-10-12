<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantPdoRepository;

class AddCompanyUuidToMerchantsTable extends TransactionalMigration
{
    public function migrate()
    {
        $this
            ->table(MerchantPdoRepository::TABLE_NAME)
            ->addColumn('company_uuid', 'string', ['null' => true, 'limit' => 36, 'after' => 'company_id'])
            ->addIndex('company_uuid')
            ->save()
        ;
    }
}
