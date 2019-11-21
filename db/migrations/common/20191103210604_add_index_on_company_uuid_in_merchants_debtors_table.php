<?php

use Phinx\Migration\AbstractMigration;

class AddIndexOnCompanyUuidInMerchantsDebtorsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants_debtors')->addIndex('company_uuid')->save();
    }
}
