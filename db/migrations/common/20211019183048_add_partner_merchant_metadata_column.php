<?php

use App\Infrastructure\Phinx\TransactionalMigration;

final class AddPartnerMerchantMetadataColumn extends TransactionalMigration
{
    public function migrate()
    {
        $this->table('orders')
            ->addColumn('partner_external_data', 'json', [
                'null' => true,
                'after' => 'debtor_external_data_id',
            ])->update();
    }
}
