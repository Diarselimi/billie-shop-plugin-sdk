<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;

final class DisableDeliveryAddressCheck extends TransactionalMigration
{
    public function migrate(): void
    {
        $this->execute('
            UPDATE `merchant_risk_check_settings` `s`
            INNER JOIN `risk_check_definitions` `d` ON `d`.`id` = `s`.`risk_check_definition_id`
            SET `s`.`enabled` = 0
            WHERE `d`.`name` LIKE \'__deprecated__%\' OR `d`.`name` = \'delivery_address\'; 
        ');
    }
}
