<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantRepository;

class DropApiKeyColumnFromMerchantsTable extends TransactionalMigration
{
    public function migrate()
    {
        $this
            ->table(MerchantRepository::TABLE_NAME)
            ->removeColumn('plain_api_key')
            ->save()
        ;
    }
}
