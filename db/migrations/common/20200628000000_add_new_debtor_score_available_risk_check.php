<?php

use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreAvailableCheck;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddNewDebtorScoreAvailableRiskCheck extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $enabled = false;
        $declineOnFailure = true;
        $this->addNewRiskCheck(DebtorScoreAvailableCheck::NAME, $enabled, $declineOnFailure);
    }
}
