<?php

use Phinx\Migration\AbstractMigration;

class AddHeaderNameToTheWebhookAuthorisationOfCurrentMerchants extends AbstractMigration
{
    public function change()
    {
        // Contorion
        $this->execute('
            UPDATE merchants 
            SET webhook_authorization = CONCAT(\'X-Api-Key: \', webhook_authorization) 
            WHERE id = 1 ANd webhook_authorization IS NOT NULL;
        ');

        // klarx
        $this->execute('
            UPDATE merchants 
            SET webhook_authorization = CONCAT(\'Authorization: Basic \', TO_BASE64(webhook_authorization)) 
            WHERE id = 2 ANd webhook_authorization IS NOT NULL;
        ');
    }
}
