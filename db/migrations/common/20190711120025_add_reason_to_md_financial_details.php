<?php

use App\Infrastructure\Repository\MerchantDebtorFinancialDetailsRepository;
use Phinx\Migration\AbstractMigration;

class AddReasonToMdFinancialDetails extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(MerchantDebtorFinancialDetailsRepository::TABLE_NAME)
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
