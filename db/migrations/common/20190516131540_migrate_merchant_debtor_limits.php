<?php

use Phinx\Migration\AbstractMigration;

class MigrateMerchantDebtorLimits extends AbstractMigration
{
    /**
     * 1. Release the limit for the debtors with "broken orders"
     * 2. Populate merchant_debtor_financial_details table with debtors and financial power
     * 3. Add the created amount to financial limit
     * 4. Add the outstanding amount to financial limit
     */
    public function change()
    {
        // This was a one-off migration using joins with Borscht DB.
        // Kept for keeping migrations log consistency.
    }
}
