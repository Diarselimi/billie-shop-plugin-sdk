<?php

use App\Infrastructure\Repository\MerchantDebtorRepository;
use Phinx\Migration\AbstractMigration;

class AddIsWhitelistedForMerchantDebtor extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantDebtorRepository::TABLE_NAME)
            ->addColumn('is_whitelisted', 'boolean', [
                'null' => false,
                'default' => false,
            ])
            ->update();
    }
}
