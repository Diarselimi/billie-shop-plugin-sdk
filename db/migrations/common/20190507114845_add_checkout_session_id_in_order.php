<?php

use App\Infrastructure\Repository\CheckoutSessionRepository;
use App\Infrastructure\Repository\OrderRepository;
use Phinx\Migration\AbstractMigration;

class AddCheckoutSessionIdInOrder extends AbstractMigration
{
    public function change()
    {
        $this->table(OrderRepository::TABLE_NAME)
            ->addColumn('checkout_session_id', 'integer', ['null' => true, 'after' => 'payment_id'])
            ->addForeignKey('checkout_session_id', CheckoutSessionRepository::TABLE_NAME, 'id')
            ->addIndex(['checkout_session_id'])
            ->save();
    }
}
