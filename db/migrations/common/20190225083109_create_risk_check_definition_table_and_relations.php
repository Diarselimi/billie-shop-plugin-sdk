<?php

use Phinx\Migration\AbstractMigration;

class CreateRiskCheckDefinitionTableAndRelations extends AbstractMigration
{
    public function change()
    {
        $this->table('risk_check_definitions')
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex('name', ['unique' => true])
            ->create()
        ;

        $this->table('merchant_risk_check_settings')
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('risk_check_definition_id', 'integer', ['null' => false])
            ->addColumn('enabled', 'boolean', ['null' => false])
            ->addColumn('decline_on_failure', 'boolean', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->addForeignKey('risk_check_definition_id', 'risk_check_definitions', 'id')
            ->addIndex(['merchant_id', 'risk_check_definition_id'], ['unique' => true])
            ->create()
        ;
    }
}
