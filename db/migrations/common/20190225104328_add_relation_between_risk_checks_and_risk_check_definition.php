<?php

use Phinx\Migration\AbstractMigration;

class AddRelationBetweenRiskChecksAndRiskCheckDefinition extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('risk_checks')
            ->addColumn('risk_check_definition_id', 'integer', ['null' => true, 'after' => 'order_id'])
            ->addForeignKey('risk_check_definition_id', 'risk_check_definitions', 'id')
            ->update()
        ;

        $this->execute('
            UPDATE risk_checks
            INNER JOIN risk_check_definitions ON risk_check_definitions.name = risk_checks.name
            SET risk_check_definition_id = risk_check_definitions.id
            WHERE risk_checks.risk_check_definition_id IS NULL
        ');

        $this->table('risk_checks')->removeColumn('name')->update();
    }
}
