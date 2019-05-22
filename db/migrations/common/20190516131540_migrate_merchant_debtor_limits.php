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
        $this->query("
            UPDATE merchants_debtors
            SET financing_limit = financing_limit + (SELECT COALESCE(SUM(orders.amount_gross), 0)
            FROM orders
            WHERE orders.state = 'new'
            AND orders.merchant_debtor_id = merchants_debtors.id
            AND EXISTS (
                SELECT * FROM order_risk_checks
                WHERE order_risk_checks.order_id = orders.id
                AND order_risk_checks.risk_check_definition_id = 6
                AND order_risk_checks.is_passed = 1
            ));
            
            INSERT INTO merchant_debtor_financial_details 
            (merchant_debtor_id, financing_limit, financing_power, created_at)
            SELECT id, financing_limit, financing_limit, NOW() FROM merchants_debtors;
            
            UPDATE merchant_debtor_financial_details
            SET merchant_debtor_financial_details.financing_limit = merchant_debtor_financial_details.financing_limit + COALESCE(
                (SELECT sum(amount_gross) FROM orders WHERE orders.state = 'created' AND orders.merchant_debtor_id = merchant_debtor_financial_details.merchant_debtor_id)
                , 0
            );
            
            UPDATE merchant_debtor_financial_details
            INNER JOIN merchants_debtors on merchant_debtor_financial_details.merchant_debtor_id = merchants_debtors.id
            INNER JOIN borscht.debtors on borscht.debtors.uuid = merchants_debtors.payment_debtor_id
            SET merchant_debtor_financial_details.financing_limit = merchant_debtor_financial_details.financing_limit + COALESCE(
                (SELECT SUM(outstanding_amount) FROM borscht.tickets WHERE borscht.tickets.debtor_id = borscht.debtors.id)
                , 0
            );
        ");
    }
}
