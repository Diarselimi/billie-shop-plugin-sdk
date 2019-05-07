<?php

use App\Infrastructure\Repository\MerchantRiskCheckSettingsRepository;
use App\Infrastructure\Repository\RiskCheckDefinitionRepository;
use Phinx\Migration\AbstractMigration;

class RemoveNameAndAddressChecksFromMerchantSettings extends AbstractMigration
{
    public function change()
    {
        $checksNames = implode(',', [
            '"debtor_name"',
            '"debtor_address"',
            '"debtor_address_street_match"',
            '"debtor_address_house_match"',
            '"debtor_address_postal_code_match"',
        ]);

        $checkIds = array_map(
            function ($row) {
                return $row['id'];
            },
            $this
            ->query("SELECT id FROM " . RiskCheckDefinitionRepository::TABLE_NAME . " WHERE `name` IN ({$checksNames})")
            ->fetchAll(PDO::FETCH_ASSOC)
        );

        if (empty($checkIds)) {
            return;
        }

        $this->execute(
            'DELETE FROM ' . MerchantRiskCheckSettingsRepository::TABLE_NAME . ' WHERE risk_check_definition_id IN(' . implode(',', $checkIds) . ')'
        );
    }
}
