<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\OrderPdoRepository;

class AddCompanyBillingAddressInOrder extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(OrderPdoRepository::TABLE_NAME)
            ->addColumn('company_billing_address_uuid', 'string', [
                'null' => true,
                'limit' => 36,
            ])
            ->save();
    }
}
