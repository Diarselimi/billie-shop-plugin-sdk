<?php

use Phinx\Migration\AbstractMigration;
use App\Infrastructure\Repository\MerchantRepository;

class AddInvestorUuidToMerchantsTable extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantRepository::TABLE_NAME)
            ->addColumn('investor_uuid', 'string', [
                'null' => false,
                'limit' => 36,
                'after' => 'oauth_client_id',
            ])
            ->save();

        $paellaDbName = getenv('DATABASE_NAME');
        $dbNameSuffix = explode('paella', $paellaDbName)[1];
        $borschtDbName = 'borscht' . $dbNameSuffix;

        try {
            $this->execute("
                UPDATE `$paellaDbName`.merchants pm
                INNER JOIN `$borschtDbName`.merchants bm ON bm.uuid = pm.payment_merchant_id
                INNER JOIN `$borschtDbName`.investors i ON i.id = bm.investor_id
                SET pm.investor_uuid = i.uuid
            ");
        } catch (PDOException $exception) {
            return;
        }
    }
}
