<?php

use Phinx\Migration\AbstractMigration;

class AlterDebtorIdentificationAlgorithmColumn extends AbstractMigration
{
    public function change()
    {
        $this->execute('UPDATE merchant_settings SET debtor_identification_algorithm = 0');
        $this
            ->table('merchant_settings')
            ->changeColumn(
                'debtor_identification_algorithm',
                'boolean',
                [
                    'null' => false,
                    'default' => false,
                ]
            )
            ->renameColumn('debtor_identification_algorithm', 'use_experimental_identification')
            ->update()
        ;
    }
}
