<?php

use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedStrictCheck;
use Phinx\Migration\AbstractMigration;

class AddNewDebtorIdentifiedStrictRiskCheck extends AbstractMigration
{
    public function change()
    {
        $riskCheckName = DebtorIdentifiedStrictCheck::NAME;
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $this
            ->table('risk_check_definitions')
            ->insert(
                [
                    'name' => $riskCheckName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            )
            ->save();

        usleep(100);

        $merchants = $this->fetchAll('SELECT * FROM merchants');

        foreach ($merchants as $merchant) {
            $merchantId = $merchant['id'];

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
                WHERE name = '{$riskCheckName}'
            ");
        }
    }
}
