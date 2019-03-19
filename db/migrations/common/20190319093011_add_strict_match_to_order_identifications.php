<?php

use Phinx\Migration\AbstractMigration;

class AddStrictMatchToOrderIdentifications extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('order_identifications')
            ->addColumn('v2_strict_match', 'boolean', ['null' => true, 'after' => 'v2_company_id'])
            ->update()
        ;
    }
}
