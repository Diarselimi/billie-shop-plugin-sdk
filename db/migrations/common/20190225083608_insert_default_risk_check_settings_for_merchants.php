<?php

use App\DomainModel\OrderRiskCheck\Checker\AmountCheck;
use App\DomainModel\OrderRiskCheck\Checker\AvailableFinancingLimitCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorBlacklistedCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorCountryCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIndustrySectorCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorOverdueCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreCheck;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use Phinx\Migration\AbstractMigration;

class InsertDefaultRiskCheckSettingsForMerchants extends AbstractMigration
{
    public function up()
    {
        $riskCheckDefinitions = [
            AvailableFinancingLimitCheck::NAME,
            AmountCheck::NAME,
            DebtorCountryCheck::NAME,
            DebtorIndustrySectorCheck::NAME,
            DebtorIdentifiedCheck::NAME,
            LimitCheck::NAME,
            DebtorBlacklistedCheck::NAME,
            DebtorOverdueCheck::NAME,
            DebtorScoreCheck::NAME,
        ];

        foreach ($riskCheckDefinitions as $riskCheckDefinitionName) {
            $this
                ->table('risk_check_definitions')
                ->insert(
                    [
                        'name' => $riskCheckDefinitionName,
                        'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                        'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                    ]
                )
                ->save();
        }

        $merchants = $this->fetchAll('SELECT * FROM merchants');

        foreach ($merchants as $merchant) {
            $merchantId = $merchant['id'];

            $this->execute("
                INSERT INTO `merchant_risk_check_settings`
                (`merchant_id`,`risk_check_definition_id`,`enabled`,`decline_on_failure`,`created_at`,`updated_at`)
                SELECT 
                  $merchantId AS merchant_id,
                  id AS risk_check_definition_id,
                  1 AS enabled,
                  1 AS decline_on_failure,
                  NOW() AS created_at,
                  NOW() AS updated_at
                FROM `risk_check_definitions`
                WHERE name <> 'debtor_address'
                ORDER BY id
            ");
        }
    }
}
