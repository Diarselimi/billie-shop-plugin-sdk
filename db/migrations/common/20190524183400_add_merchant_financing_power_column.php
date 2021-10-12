<?php

use App\Infrastructure\Repository\MerchantPdoRepository;
use Phinx\Migration\AbstractMigration;

class AddMerchantFinancingPowerColumn extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantPdoRepository::TABLE_NAME)
            ->addColumn(
                'financing_power',
                'decimal',
                [
                    'null' => true,
                    'precision' => 20,
                    'scale' => 2,
                    'after' => 'name',
                ]
            )
            ->update();
        // Move values to financing_power column
        $replaceValuesSql = 'UPDATE '. MerchantPdoRepository::TABLE_NAME .' SET financing_power = available_financing_limit';
        $this->execute($replaceValuesSql);

        $sql = 'ALTER TABLE '. MerchantPdoRepository::TABLE_NAME .' MODIFY financing_power DECIMAL(20,2) NOT NULL';
        $this->execute($sql);
    }
}
