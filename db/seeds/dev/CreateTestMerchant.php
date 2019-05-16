<?php

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use Phinx\Seed\AbstractSeed;

class CreateTestMerchant extends AbstractSeed
{
    public function run()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $this->table('merchants')->insert([
            'name' => 'Contorion',
            'available_financing_limit' => 2000000,
            'api_key' => 'billie',
            'roles' => json_encode(MerchantEntity::DEFAULT_ROLES),
            'is_active' => true,
            'company_id' => 4,
            'payment_merchant_id' => 'b95adad7-f747-45b9-b3cb-7851c4b90fac',
            'created_at' => $now,
            'updated_at' => $now,
        ])->saveData();

        $merchantId = $this->getAdapter()->getConnection()->lastInsertId();

        $this->table('score_thresholds_configuration')->insert([
            'crefo_low_score_threshold' => ScoreThresholdsConfigurationEntityFactory::DEFAULT_CREFO_LOW,
            'crefo_high_score_threshold' => ScoreThresholdsConfigurationEntityFactory::DEFAULT_CREFO_HIGH,
            'schufa_low_score_threshold' => ScoreThresholdsConfigurationEntityFactory::DEFAULT_SCHUFA_LOW,
            'schufa_average_score_threshold' => ScoreThresholdsConfigurationEntityFactory::DEFAULT_SCHUFA_AVERAGE,
            'schufa_high_score_threshold' => ScoreThresholdsConfigurationEntityFactory::DEFAULT_SCHUFA_HIGH,
            'schufa_sole_trader_score_threshold' => ScoreThresholdsConfigurationEntityFactory::DEFAULT_SCHUFA_SOLE_TRADER,
            'created_at' => $now,
            'updated_at' => $now,
        ])->saveData();

        $id = $this->getAdapter()->getConnection()->lastInsertId();
        $defaultStrategy = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE;

        $this->execute("
            INSERT INTO merchant_settings (
                merchant_id, 
                initial_debtor_financing_limit,
                debtor_financing_limit, 
                min_order_amount, 
                score_thresholds_configuration_id, 
                invoice_handling_strategy, 
                created_at, 
                updated_at
            )
            SELECT merchants.id as merchant_id,
                7500 as initial_debtor_financing_limit, 
                7500 as debtor_financing_limit,
                0 as min_order_amount,
                {$id} as score_thresholds_configuration_id,
                '{$defaultStrategy}' as invoice_handling_strategy,
                '{$now}' as created_at,
                '{$now}' as updated_at
            FROM merchants WHERE merchants.id NOT IN (SELECT merchant_id FROM merchant_settings);
        ");

        $this->execute("
                INSERT INTO `merchant_risk_check_settings`
                (`merchant_id`,`risk_check_definition_id`,`enabled`,`decline_on_failure`,`created_at`,`updated_at`)
                SELECT 
                  {$merchantId} AS merchant_id,
                  id AS risk_check_definition_id,
                  1 AS enabled,
                  1 AS decline_on_failure,
                  NOW() AS created_at,
                  NOW() AS updated_at
                FROM `risk_check_definitions`
                WHERE name <> 'debtor_address'
        ");
    }
}
