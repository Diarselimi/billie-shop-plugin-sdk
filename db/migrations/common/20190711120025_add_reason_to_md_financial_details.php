<?php

use Phinx\Migration\AbstractMigration;

class AddReasonToMdFinancialDetails extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('merchant_debtor_financial_details')
            ->addColumn(
                'reason',
                'string',
                [
                    'null' => true,
                    'limit' => 20,
                    'after' => 'financing_power',
                ]
            )->update();
    }
}
