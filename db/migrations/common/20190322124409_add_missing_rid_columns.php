<?php

use Phinx\Migration\AbstractMigration;

class AddMissingRidColumns extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('orders')
            ->addColumn(
                'rid',
                'string',
                [
                    'null' => true,
                    'limit' => 36,
                    'after' => 'uuid',
                ]
            )->update();
    }
}
