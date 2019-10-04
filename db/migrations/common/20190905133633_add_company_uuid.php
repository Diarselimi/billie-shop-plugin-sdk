<?php

use App\Infrastructure\Repository\MerchantDebtorRepository;
use Phinx\Migration\AbstractMigration;

class AddCompanyUuid extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantDebtorRepository::TABLE_NAME)
            ->addColumn('company_uuid', 'string', [
                'null' => true,
                'limit' => 36,
                'after' => 'debtor_id',
            ])
            ->save();
    }
}
