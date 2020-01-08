<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantRepository;

class SetMerchantCompanyUuidToNotNull extends TransactionalMigration
{
    public function migrate()
    {
        $this
            ->table(MerchantRepository::TABLE_NAME)
            ->changeColumn('company_uuid', 'string', ['null' => false, 'limit' => 36])
            ->save()
        ;
    }
}
