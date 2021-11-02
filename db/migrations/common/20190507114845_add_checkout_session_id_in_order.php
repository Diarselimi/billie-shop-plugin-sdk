<?php

use App\Infrastructure\Repository\OrderPdoRepository;
use Phinx\Migration\AbstractMigration;

class AddCheckoutSessionIdInOrder extends AbstractMigration
{
    public function change()
    {
        $this->table(OrderPdoRepository::TABLE_NAME)
            ->addColumn('checkout_session_id', 'integer', ['null' => true, 'after' => 'payment_id'])
            ->addForeignKey('checkout_session_id', 'checkout_sessions', 'id')
            ->addIndex(['checkout_session_id'])
            ->save();
    }
}
