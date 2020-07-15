<?php

namespace App\Infrastructure\Phinx;

use App\Infrastructure\Repository\MerchantRiskCheckSettingsRepository;
use App\Infrastructure\Repository\RiskCheckDefinitionRepository;
use Phinx\Db\Table;

trait MigrationHelperTrait
{
    protected function buildInClauseQuotedString(array $strings): string
    {
        return implode(', ', array_map(function ($roleName) {
            return "'{$roleName}'";
        }, $strings));
    }

    protected function buildAppendRolePermissionSql(string $permissionName, array $roleNames): string
    {
        return sprintf(
            "UPDATE merchant_user_roles SET permissions = JSON_ARRAY_APPEND(permissions, '$', '%s') WHERE `name` IN (%s)",
            $permissionName,
            $this->buildInClauseQuotedString($roleNames)
        );
    }

    protected function setupStateTransitionTableCreation(Table $table, string $fkFieldName, string $fkTableName): Table
    {
        return $table
            ->addColumn('transition', 'string', ['null' => false])
            ->addColumn('from', 'string', ['null' => true])
            ->addColumn('to', 'string', ['null' => false])
            ->addColumn($fkFieldName, 'integer', ['null' => false])
            ->addColumn('transited_at', 'datetime', ['null' => false])
            ->addForeignKey($fkFieldName, $fkTableName, 'id');
    }

    public function addNewRiskCheck(string $riskCheckName, bool $enabled, bool $declineOnFailure)
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $definition = [
            'name' => $riskCheckName,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $this->table(RiskCheckDefinitionRepository::TABLE_NAME)
            ->insert($definition)
            ->save();

        $riskCheckId = $this->getAdapter()->getConnection()->lastInsertId();

        // Add the new Risk Check for the merchants that already have other risk checks
        $merchantsWithExistingRiskChecks = $this->fetchAll(
            'SELECT merchant_id FROM ' . MerchantRiskCheckSettingsRepository::TABLE_NAME . ' GROUP BY merchant_id'
        );

        foreach ($merchantsWithExistingRiskChecks as $row) {
            $values = [
                'merchant_id' => (int) $row['merchant_id'],
                'risk_check_definition_id' => $riskCheckId,
                'enabled' => intval($enabled),
                'decline_on_failure' => intval($declineOnFailure),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $this->table(MerchantRiskCheckSettingsRepository::TABLE_NAME)
                ->insert($values)
                ->save();
        }
    }
}
