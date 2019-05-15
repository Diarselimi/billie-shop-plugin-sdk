<?php


use Phinx\Migration\AbstractMigration;

class ChangeAmountForgivenToNotNullable extends AbstractMigration
{
    public function change()
    {
        $this->execute('UPDATE orders SET amount_forgiven = 0 WHERE amount_forgiven IS NULL');

        $this
            ->table('orders')
            ->changeColumn('amount_forgiven', 'float', ['null' => false, 'default' => 0])
            ->update();
    }
}
