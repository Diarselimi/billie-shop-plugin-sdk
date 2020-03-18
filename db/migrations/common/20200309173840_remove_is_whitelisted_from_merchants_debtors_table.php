<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantDebtorRepository;

class RemoveIsWhitelistedFromMerchantsDebtorsTable extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(MerchantDebtorRepository::TABLE_NAME)
            ->removeColumn('is_whitelisted')
            ->update();
    }
}
