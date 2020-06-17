<?php

use App\DomainModel\OrderRiskCheck\Checker\FraudScoreCheck;
use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantRiskCheckSettingsRepository;
use App\Infrastructure\Repository\RiskCheckDefinitionRepository;

class AddNewFraudScoreRiskCheck extends TransactionalMigration
{
    public function migrate()
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $this->table(RiskCheckDefinitionRepository::TABLE_NAME)
            ->insert([
                'name' => FraudScoreCheck::NAME,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->save();
        $riskCheckId = $this->getAdapter()->getConnection()->lastInsertId();

        //Enable the new riskcheck for existing merchants
        $merchantsWithexistingRiskChecks = $this->fetchAll('SELECT * FROM ' . MerchantRiskCheckSettingsRepository::TABLE_NAME . ' GROUP BY merchant_id');

        foreach ($merchantsWithexistingRiskChecks as $merchantsWithexistingRiskCheck) {
            $values = [
                'merchant_id' => (int) $merchantsWithexistingRiskCheck['merchant_id'],
                'risk_check_definition_id' => $riskCheckId,
                'enabled' => 1,
                'decline_on_failure' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $this->table(MerchantRiskCheckSettingsRepository::TABLE_NAME)
                ->insert($values)
                ->save();
        }
    }
}
