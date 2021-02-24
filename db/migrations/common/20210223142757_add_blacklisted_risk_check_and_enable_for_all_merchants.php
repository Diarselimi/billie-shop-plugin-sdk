<?php

use App\DomainModel\OrderRiskCheck\Checker\BlackListAddressCheck;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddBlacklistedRiskCheckAndEnableForAllMerchants extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $this->addNewRiskCheck(BlackListAddressCheck::NAME, true, true);
    }
}
