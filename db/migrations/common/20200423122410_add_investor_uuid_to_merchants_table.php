<?php

use App\Infrastructure\Repository\MerchantRepository;
use Phinx\Migration\AbstractMigration;

class AddInvestorUuidToMerchantsTable extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantRepository::TABLE_NAME)
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
