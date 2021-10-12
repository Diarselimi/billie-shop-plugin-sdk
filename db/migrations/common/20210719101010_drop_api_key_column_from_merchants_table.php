<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantPdoRepository;

class DropApiKeyColumnFromMerchantsTable extends TransactionalMigration
{
    public function migrate()
    {
        $this
            ->table(MerchantPdoRepository::TABLE_NAME)
            ->removeColumn('plain_api_key')
            ->save()
        ;
    }
}
