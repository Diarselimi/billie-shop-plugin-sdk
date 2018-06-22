<?php

use Phinx\Migration\AbstractMigration;

class AddProofOfDeliveryUrl extends AbstractMigration
{
    public function change()
    {
        $this->table('orders')
            ->addColumn('proof_of_delivery_url', 'string', ['null' => true, 'after' => 'invoice_url'])
            ->update();
    }
}
