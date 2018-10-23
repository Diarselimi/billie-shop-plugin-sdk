<?php

use Phinx\Migration\AbstractMigration;

class MigrateExtAndPaymentIds extends AbstractMigration
{
    public function change()
    {
        // have to put all queries in one, otherwise database goes away
        $this->execute('
            # reassign the order with the same debtor to the latest one
            UPDATE orders
            SET merchant_debtor_id = (
                SELECT proper_merchant_debtor.id
                FROM merchants_debtors proper_merchant_debtor
                WHERE proper_merchant_debtor.debtor_id = (
                    SELECT duplicated_merchant_debtor.debtor_id
                    FROM merchants_debtors duplicated_merchant_debtor
                    WHERE duplicated_merchant_debtor.id = orders.merchant_debtor_id
                )
                ORDER BY id DESC
                LIMIT 1
            )
            WHERE merchant_debtor_id IS NOT NULL;
            
            # kill duplicated debtors
            DELETE FROM target 
            USING merchants_debtors as target
            WHERE target.debtor_id IN (
                SELECT source.debtor_id
                FROM (SELECT * FROM merchants_debtors) AS source
                WHERE source.debtor_id = target.debtor_id
                AND source.id > target.id
            );
            
            #copy debtor payment ids from webapp to core
            UPDATE paella.merchants_debtors 
            INNER JOIN webapp.companies ON webapp.companies.id = paella.merchants_debtors.debtor_id
            SET paella.merchants_debtors.payment_debtor_id = webapp.companies.payment_id;
        ');
    }
}
