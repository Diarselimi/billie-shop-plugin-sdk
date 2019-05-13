<?php

use App\Infrastructure\Repository\RiskCheckDefinitionRepository;
use Phinx\Migration\AbstractMigration;

class RenameDeprecatedNameAndAddressRiskChecks extends AbstractMigration
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

        $checks = $this->query("SELECT id, `name` FROM " . RiskCheckDefinitionRepository::TABLE_NAME . " WHERE `name` IN ({$checksNames})")
            ->fetchAll(PDO::FETCH_ASSOC);

        if (empty($checks)) {
            return;
        }

        $updatedAt = (new \DateTime())->format(RiskCheckDefinitionRepository::DATE_FORMAT);

        $sqls = [];
        foreach ($checks as $checkData) {
            $sqls[] = "UPDATE " . RiskCheckDefinitionRepository::TABLE_NAME .
                " SET `name`='__deprecated__{$checkData['name']}', updated_at='{$updatedAt}' WHERE id={$checkData['id']}";
        }

        $this->execute(implode(";\n", $sqls));
    }
}
