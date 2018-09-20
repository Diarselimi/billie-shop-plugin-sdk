<?php

use Phinx\Migration\AbstractMigration;

class AddDebtorIdValidation extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants_debtors')
            ->addColumn('debtor_id_validation', 'boolean', ['default' => false, 'after' => 'external_id'])
            ->update()
        ;

        $this->execute('UPDATE merchants_debtors SET debtor_id_validation = 1');
    }
}
