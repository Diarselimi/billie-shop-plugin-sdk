<?php

use App\Infrastructure\Repository\DebtorExternalDataRepository;
use Phinx\Migration\AbstractMigration as AbstractMigrationAlias;

class AddHashColumn extends AbstractMigrationAlias
{
    public function change()
    {
        $this->table(DebtorExternalDataRepository::TABLE_NAME)
            ->addColumn('debtor_data_hash', 'string', [
                'null' => true,
                'limit' => 32,
                'after' => 'merchant_external_id',
            ])
            ->addIndex(['debtor_data_hash'], ['name' => 'debtor_data_hash_index'])
            ->update();

        $this->execute('UPDATE '. DebtorExternalDataRepository::TABLE_NAME .' SET debtor_data_hash = MD5(id);');
        $this->execute('ALTER TABLE '. DebtorExternalDataRepository::TABLE_NAME .' CHANGE COLUMN  debtor_data_hash debtor_data_hash varchar(32) NOT NULL;');
    }
}
