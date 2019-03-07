<?php

use Phinx\Migration\AbstractMigration;

class RenameRiskChecksTableToOrderRiskChecks extends AbstractMigration
{
    public function change()
    {
        $this->table('risk_checks')->rename('order_risk_checks');
    }
}
