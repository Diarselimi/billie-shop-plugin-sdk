<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRepository;

class MerchantUserSignatoryPowersUuid extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(MerchantUserRepository::TABLE_NAME)
            ->addColumn('signatory_power_uuid', 'string', ['null' => true, 'limit' => 36])
            ->save();
    }
}
