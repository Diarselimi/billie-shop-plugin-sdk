<?php

use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use Phinx\Migration\AbstractMigration;

class TestMerchant extends AbstractMigration
{
    public function up()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $this->table('merchants')->insert([
            'name' => 'Contorion',
            'available_financing_limit' => 2000000,
            'api_key' => 'billie',
            'roles' => '["ROLE_API_USER", "ROLE_CAN_HANDLE_INVOICES"]',
            'is_active' => true,
            'company_id' => 4,
            'payment_merchant_id' => 'b95adad7-f747-45b9-b3cb-7851c4b90fac',
            'created_at' => $now,
            'updated_at' => $now,
        ])->saveData();

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

        $this->execute("
            INSERT INTO merchant_settings (merchant_id, debtor_financing_limit, min_order_amount, score_thresholds_configuration_id, created_at, updated_at)
            SELECT merchants.id as merchant_id, 
                   7500 as debtor_financing_limit,
                   0 as min_order_amount,
                   {$id} as score_thresholds_configuration_id,
                   '{$now}' as created_at,
                   '{$now}' as updated_at
            FROM merchants WHERE merchants.id NOT IN (SELECT merchant_id FROM merchant_settings);
        ");
    }
}
