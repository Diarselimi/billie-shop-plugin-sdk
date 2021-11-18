<?php

use App\Infrastructure\Phinx\TransactionalMigration;

final class AddTableForShipmentAndMigrateOldData extends TransactionalMigration
{
    public function migrate(): void
    {
        $this->table('shipping_infos')
            ->addColumn('invoice_uuid', 'string', ['length' => 36])
            ->addColumn('return_shipping_company', 'string', ['null' => true])
            ->addColumn('return_tracking_number', 'string', ['null' => true])
            ->addColumn('return_tracking_url', 'string', ['null' => true])
            ->addColumn('shipping_company', 'string', ['null' => true])
            ->addColumn('shipping_method', 'string', ['null' => true])
            ->addColumn('tracking_number', 'string', ['null' => true])
            ->addColumn('tracking_url', 'string', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->create();

        //migrate query
        $sql = <<<SQL
INSERT INTO shipping_infos
SELECT 
		null as id,
       i.invoice_uuid as invoice_uuid, 
       null as return_shipping_company,
       null as return_tracking_url,
       null as return_shipping_company,
       null as shipping_method,
       null as tracking_number,
       orders.proof_of_delivery_url as tracking_url 
from orders 
LEFT JOIN order_invoices_v2 i ON i.order_id = orders.id
WHERE proof_of_delivery_url is not null;
SQL;
        $this->execute($sql);

        $this->table('orders')
            ->removeColumn('proof_of_delivery_url')
            ->save();
    }
}
