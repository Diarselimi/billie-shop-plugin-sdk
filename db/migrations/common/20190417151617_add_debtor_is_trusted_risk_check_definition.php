<?php

use App\DomainModel\OrderRiskCheck\Checker\DebtorIsTrustedCheck;
use App\Infrastructure\Repository\MerchantPdoRepository;
use App\Infrastructure\Repository\MerchantRiskCheckSettingsRepository;
use App\Infrastructure\Repository\RiskCheckDefinitionRepository;
use Phinx\Migration\AbstractMigration;

class AddDebtorIsTrustedRiskCheckDefinition extends AbstractMigration
{
    public function change()
    {
        $currentDatetime = (new DateTime())->format('Y-m-d H:i:s');
        $this->table(RiskCheckDefinitionRepository::TABLE_NAME)->insert([
            'name' => DebtorIsTrustedCheck::NAME,
            'created_at' => $currentDatetime,
            'updated_at' => $currentDatetime,
        ])->save();

        $definitionRow = $this->fetchRow("SELECT * FROM ".RiskCheckDefinitionRepository::TABLE_NAME." WHERE name = '".DebtorIsTrustedCheck::NAME."'");

        $merchantIds = $this->fetchAll("SELECT id AS merchant_id FROM ". MerchantPdoRepository::TABLE_NAME);

        foreach ($merchantIds as $row) {
            $this->table(MerchantRiskCheckSettingsRepository::TABLE_NAME)->insert([
                'merchant_id' => $row['merchant_id'],
                'risk_check_definition_id' => $definitionRow['id'],
                'enabled' => true,
                'decline_on_failure' => true,
                'created_at' => $currentDatetime,
                'updated_at' => $currentDatetime,
            ])->save();
        }
    }
}
