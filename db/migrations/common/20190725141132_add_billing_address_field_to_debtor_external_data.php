<?php

use App\Infrastructure\Repository\AddressRepository;
use App\Infrastructure\Repository\DebtorExternalDataRepository;
use Phinx\Migration\AbstractMigration;

class AddBillingAddressFieldToDebtorExternalData extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(DebtorExternalDataRepository::TABLE_NAME)
            ->addColumn(
                'billing_address_id',
                'integer',
                [
                    'null' => true,
                    'after' => 'address_id',
                ]
            )
            ->addForeignKey(
                'billing_address_id',
                AddressRepository::TABLE_NAME,
                'id'
            )
            ->update();

        $sql = 'UPDATE ' . DebtorExternalDataRepository::TABLE_NAME . ' SET billing_address_id = address_id';
        $this->execute($sql);

        $this
            ->table(DebtorExternalDataRepository::TABLE_NAME)
            ->changeColumn('billing_address_id', 'int', ['null' => false])
            ->save()
        ;
    }
}
