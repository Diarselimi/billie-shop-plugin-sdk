<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantFinancialAssessmentRepository;

class AddMerchantFinancialDetailsTable extends TransactionalMigration
{
    public function migrate()
    {
        $this->table(MerchantFinancialAssessmentRepository::TABLE_NAME)
            ->addColumn('data', 'json', ['null' => false])
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->create();
    }
}
