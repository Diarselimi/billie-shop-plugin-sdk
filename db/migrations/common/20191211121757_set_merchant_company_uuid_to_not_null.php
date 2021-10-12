<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantPdoRepository;

class SetMerchantCompanyUuidToNotNull extends TransactionalMigration
{
    public function migrate()
    {
        $this
            ->table(MerchantPdoRepository::TABLE_NAME)
            ->changeColumn('company_uuid', 'string', ['null' => false, 'limit' => 36])
            ->save()
        ;
    }
}
