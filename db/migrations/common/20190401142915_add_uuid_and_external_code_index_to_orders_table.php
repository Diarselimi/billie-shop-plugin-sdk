<?php

use Phinx\Migration\AbstractMigration;

class AddUuidAndExternalCodeIndexToOrdersTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('orders')
            ->addIndex(['external_code'], ['name' => 'order_external_code'])
            ->addIndex(['uuid'], ['name' => 'order_uuid', 'unique' => true])
            ->update()
        ;
    }
}
