<?php

use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedBillingAddressCheck;
use App\Infrastructure\Phinx\TransactionalMigration;

class EnableDebtorIdentifiedBillingAddressRiskCheckForAllMerchants extends TransactionalMigration
{
    public function migrate()
    {
        $riskCheckName = DebtorIdentifiedBillingAddressCheck::NAME;

        $this->execute("
            UPDATE `merchant_risk_check_settings` mrcs
            JOIN `risk_check_definitions` rcd ON mrcs.risk_check_definition_id = rcd.id
            SET mrcs.enabled = 1
            WHERE rcd.name = '{$riskCheckName}'
        ");
    }
}
