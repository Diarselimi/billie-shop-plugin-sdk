<?php

use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddBlacklistedRiskCheckAndEnableForAllMerchants extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $this->addNewRiskCheck('black_listed_address', true, true);
    }
}
