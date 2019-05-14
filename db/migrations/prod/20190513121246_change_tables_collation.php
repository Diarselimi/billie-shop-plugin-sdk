<?php

use Phinx\Migration\AbstractMigration;

class ChangeTablesCollation extends AbstractMigration
{
    public function change()
    {
        $this->execute('ALTER TABLE addresses CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE debtor_external_data CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE merchant_risk_check_settings CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE merchant_settings CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE merchant_users CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE merchants CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE merchants_debtors CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE merchants_debtors_duplicates CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE order_identifications CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE order_invoices CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE order_notification_deliveries CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE order_notifications CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE order_risk_checks CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE order_transitions CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE orders CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE persons CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE risk_check_definitions CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('ALTER TABLE score_thresholds_configuration CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
    }
}
