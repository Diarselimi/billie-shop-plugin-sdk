<?php

use App\Infrastructure\Repository\MerchantPdoRepository;
use Phinx\Migration\AbstractMigration;

class AddInvestorUuidToMerchantsTable extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantPdoRepository::TABLE_NAME)
            ->addColumn(
                'investor_uuid',
                'string',
                [
                    'null' => false,
                    'limit' => 36,
                    'after' => 'oauth_client_id',
                ]
            )
            ->save();
    }
}
