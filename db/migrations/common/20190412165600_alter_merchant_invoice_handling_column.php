<?php

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use Phinx\Migration\AbstractMigration;

class AlterMerchantInvoiceHandlingColumn extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('merchant_settings')
            ->addColumn(
                'invoice_handling_strategy',
                'string',
                [
                    'limit' => 12,
                    'after' => 'use_experimental_identification',
                    'null' => true,
                ]
            )
            ->update()
        ;

        $defaultStrategy = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE;
        $this->execute("UPDATE merchant_settings SET invoice_handling_strategy = '{$defaultStrategy}'");

        $this
            ->table('merchant_settings')
            ->changeColumn(
                'invoice_handling_strategy',
                'string',
                ['null' => false]
            )
            ->update()
        ;
    }
}
