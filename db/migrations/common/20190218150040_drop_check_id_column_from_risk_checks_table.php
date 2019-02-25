<?php

use Phinx\Migration\AbstractMigration;

class DropCheckIdColumnFromRiskChecksTable extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('risk_checks')
            ->removeColumn('check_id')
            ->save()
        ;
    }

    public function down()
    {
        $this
            ->table('risk_checks')
            ->addColumn('check_id', 'integer', ['null' => true])
            ->update()
        ;
    }
}
