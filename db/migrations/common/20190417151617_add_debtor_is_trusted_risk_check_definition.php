<?php

use App\DomainModel\OrderRiskCheck\Checker\DebtorIsTrusted;
use App\Infrastructure\Repository\MerchantRepository;
use App\Infrastructure\Repository\MerchantRiskCheckSettingsRepository;
use App\Infrastructure\Repository\RiskCheckDefinitionRepository;
use Phinx\Migration\AbstractMigration;

class AddDebtorIsTrustedRiskCheckDefinition extends AbstractMigration
{
    public function change()
    {
        $currentDatetime = (new DateTime())->format('Y-m-d H:i:s');
        $this->insert(RiskCheckDefinitionRepository::TABLE_NAME, [
            'name' => DebtorIsTrusted::NAME,
            'created_at' => $currentDatetime,
            'updated_at' => $currentDatetime,
        ]);

        $definitionRow = $this->fetchRow("SELECT * FROM ".RiskCheckDefinitionRepository::TABLE_NAME." WHERE name = '".DebtorIsTrusted::NAME."'");

        $merchantIds = $this->fetchAll("SELECT id AS merchant_id FROM ". MerchantRepository::TABLE_NAME);

        foreach ($merchantIds as $row) {
            $this->insert(MerchantRiskCheckSettingsRepository::TABLE_NAME, [
                'merchant_id' => $row['merchant_id'],
                'risk_check_definition_id' => $definitionRow['id'],
                'enabled' => true,
                'decline_on_failure' => true,
                'created_at' => $currentDatetime,
                'updated_at' => $currentDatetime,
            ]);
        }
    }
}
