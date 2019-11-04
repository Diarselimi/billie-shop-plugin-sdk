<?php

use App\DomainModel\OrderRiskCheck\Checker\LineItemsCheck;
use App\Infrastructure\Repository\MerchantRiskCheckSettingsRepository;
use App\Infrastructure\Repository\RiskCheckDefinitionRepository;
use Phinx\Migration\AbstractMigration;

class AddNewFraudRiskCheck extends AbstractMigration
{
    public function up()
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $this->table(RiskCheckDefinitionRepository::TABLE_NAME)
            ->insert([
                'name' => LineItemsCheck::NAME,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->save();
        $riskCheckId = $this->getAdapter()->getConnection()->lastInsertId();

        $merchantsWithexistingRiskChecks = $this->fetchAll('SELECT * FROM '. MerchantRiskCheckSettingsRepository::TABLE_NAME . ' GROUP BY merchant_id');

        foreach ($merchantsWithexistingRiskChecks as $merchantsWithexistingRiskCheck) {
            $values = [
                'merchant_id' => (int) $merchantsWithexistingRiskCheck['merchant_id'],
                'risk_check_definition_id' => $riskCheckId,
                'enabled' => 0,
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
