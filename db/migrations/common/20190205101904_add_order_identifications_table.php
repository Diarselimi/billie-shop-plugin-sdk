<?php

use Phinx\Migration\AbstractMigration;

class AddOrderIdentificationsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('order_identifications')
             ->addColumn('order_id', 'integer', ['null' => false])
             ->addColumn('v1_company_id', 'integer', ['null' => true])
             ->addColumn('v2_company_id', 'integer', ['null' => true])
             ->addColumn('created_at', 'datetime', ['null' => false])
             ->addColumn('updated_at', 'datetime', ['null' => false])
             ->addForeignKey('order_id', 'orders', 'id')
             ->create()
        ;
    }
}
