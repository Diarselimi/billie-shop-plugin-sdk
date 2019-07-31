<?php

use App\Infrastructure\Repository\MerchantRepository;
use Phinx\Migration\AbstractMigration;

class AddMerchantFinancingPowerColumn extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantRepository::TABLE_NAME)
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
        $replaceValuesSql = 'UPDATE '. MerchantRepository::TABLE_NAME .' SET financing_power = available_financing_limit';
        $this->execute($replaceValuesSql);

        $sql = 'ALTER TABLE '. MerchantRepository::TABLE_NAME .' MODIFY financing_power DECIMAL(20,2) NOT NULL';
        $this->execute($sql);
    }
}
