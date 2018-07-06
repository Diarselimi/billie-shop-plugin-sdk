<?php

use Phinx\Migration\AbstractMigration;

class MakeRiskCheckIdOptional extends AbstractMigration
{
    public function change()
    {
        $this->table('risk_checks')
            ->changeColumn('check_id', 'integer', ['null' => true])
            ->update()
        ;
    }
}
