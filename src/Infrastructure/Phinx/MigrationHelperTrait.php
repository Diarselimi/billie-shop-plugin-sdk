<?php

namespace App\Infrastructure\Phinx;

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
}
