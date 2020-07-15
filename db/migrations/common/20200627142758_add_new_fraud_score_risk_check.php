<?php

use App\DomainModel\OrderRiskCheck\Checker\FraudScoreCheck;
use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;

class AddNewFraudScoreRiskCheck extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $enabled = true;
        $declineOnFailure = false;
        $this->addNewRiskCheck(FraudScoreCheck::NAME, $enabled, $declineOnFailure);
    }
}
