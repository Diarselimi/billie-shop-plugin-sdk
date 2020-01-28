<?php

use App\DomainModel\MerchantOnboarding\MerchantOnboardingEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\Infrastructure\Repository\MerchantOnboarding\MerchantOnboardingRepository;
use Phinx\Seed\AbstractSeed;

class Seed001AddMerchants extends AbstractSeed
{
    public function run()
    {
        $testApiKey = 'billie';

        if ($this->merchantExists($testApiKey)) {
            return;
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $this->table('merchants')->insert([
            'name' => 'Test Contorion',
            'financing_power' => 2000000,
            'available_financing_limit' => 2000000,
            'api_key' => $testApiKey,
            'is_active' => true,
            'company_id' => 4,
            'company_uuid' => 'b825f0a8-7248-477f-b827-88eb927fb7c1',
            'oauth_client_id' => '02706840-e7ef-48ef-8576-bcfec20b4458',
            'payment_merchant_id' => 'b95adad7-f747-45b9-b3cb-7851c4b90fac',
            'created_at' => $now,
            'updated_at' => $now,
        ])->saveData();

        $merchantId = (int) $this->getAdapter()->getConnection()->lastInsertId();

        $scoreThresholdsId = $this->createDefaultScoreSettings($now);
        $this->createDefaultSettingsForAllMerchants($now, $scoreThresholdsId);
        $this->createRiskCheckSettings($now, $merchantId);
        $this->createDefaultOnboarding($merchantId, MerchantOnboardingEntity::STATE_COMPLETE);
    }

    private function createRiskCheckSettings(string $now, int $merchantId)
    {
        $this->execute("
                INSERT INTO `merchant_risk_check_settings`
                (`merchant_id`,`risk_check_definition_id`,`enabled`,`decline_on_failure`,`created_at`,`updated_at`)
                SELECT 
                  {$merchantId} AS merchant_id,
                  id AS risk_check_definition_id,
                  1 AS enabled,
                  1 AS decline_on_failure,
                  '{$now}' AS created_at,
                  '{$now}' AS updated_at
                FROM `risk_check_definitions`
                WHERE `name` <> 'debtor_address'
        ");
    }

    private function createDefaultSettingsForAllMerchants(string $now, int $scoreThresholdsId)
    {
        $invoiceHandlingStrategy = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE;

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
                {$scoreThresholdsId} as score_thresholds_configuration_id,
                '{$invoiceHandlingStrategy}' as invoice_handling_strategy,
                '{$now}' as created_at,
                '{$now}' as updated_at
            FROM merchants WHERE merchants.id NOT IN (SELECT merchant_id FROM merchant_settings);
        ");
    }

    private function createDefaultScoreSettings(string $now): int
    {
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

        return (int) $this->getAdapter()->getConnection()->lastInsertId();
    }

    private function merchantExists($apiKey): bool
    {
        $stmt = $this->query("SELECT id FROM merchants WHERE api_key='{$apiKey}'");
        $result = $stmt ? $stmt->fetch() : null;

        return !!$result;
    }

    private function createDefaultOnboarding(int $merchantId, string $state)
    {
        $table = MerchantOnboardingRepository::TABLE_NAME;
        $dateTime = (new DateTime())->format('Y-m-d H:i:s');

        $this->execute("
            INSERT INTO {$table} (`uuid`, `merchant_id`, `state`, `created_at`, `updated_at`)
                VALUES (UUID(), {$merchantId}, '{$state}', '{$dateTime}', '{$dateTime}');
        ");
    }
}
