<?php

use App\Infrastructure\Repository\MerchantDebtorRepository;
use Phinx\Migration\AbstractMigration;
use Ramsey\Uuid\Uuid;

class AddUuidToMerchantDebtors extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(MerchantDebtorRepository::TABLE_NAME)
            ->addColumn(
                'uuid',
                'string',
                [
                    'null' => true,
                    'limit' => 36,
                    'after' => 'payment_debtor_id',
                ]
            )->update();

        /** @var PDOStatement $stmt */
        $stmt = $this->query('SELECT id FROM ' . MerchantDebtorRepository::TABLE_NAME . ' WHERE uuid IS NULL');
        while ($merchantDebtorId = $stmt->fetchColumn(0)) {
            $uuid = Uuid::uuid4()->toString();
            while ($this->query('SELECT COUNT(id) FROM ' . MerchantDebtorRepository::TABLE_NAME . " WHERE uuid='{$uuid}'")->fetchColumn(0) > 0) {
                $uuid = Uuid::uuid4()->toString();
            }
            $this->execute('UPDATE ' . MerchantDebtorRepository::TABLE_NAME . " SET uuid = '{$uuid}' WHERE id={$merchantDebtorId} LIMIT 1");
        }

        $this
            ->table(MerchantDebtorRepository::TABLE_NAME)
            ->changeColumn('uuid', 'string', ['null' => false, 'limit' => 36])
            ->update();
    }
}
