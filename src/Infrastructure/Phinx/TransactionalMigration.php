<?php

namespace App\Infrastructure\Phinx;

use Phinx\Migration\AbstractMigration;

abstract class TransactionalMigration extends AbstractMigration
{
    final public function change()
    {
        $adapter = $this->getAdapter();

        if (!$adapter->hasTransactions()) {
            $this->migrate();

            return;
        }

        $adapter->beginTransaction();

        try {
            $this->migrate();
            $adapter->commitTransaction();
        } catch (\Throwable $e) {
            $adapter->rollbackTransaction();

            throw $e;
        }
    }

    abstract protected function migrate();
}
