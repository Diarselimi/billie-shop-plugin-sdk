<?php

use App\Infrastructure\Repository\MerchantSettingsRepository;
use Phinx\Migration\AbstractMigration;

class AlterDebtorIdentificationAlgorithmColumn extends AbstractMigration
{
    public function change()
    {
        $table = MerchantSettingsRepository::TABLE_NAME;
        $this->execute('UPDATE ' . $table . ' SET debtor_identification_algorithm = 0');
        $this
            ->table($table)
            ->changeColumn(
                'debtor_identification_algorithm',
                'boolean',
                [
                    'null' => false,
                    'default' => false,
                ]
            )
            ->update()
        ;

        $this
            ->table($table)
            ->renameColumn('debtor_identification_algorithm', 'use_experimental_identification')
            ->update()
        ;
    }
}
