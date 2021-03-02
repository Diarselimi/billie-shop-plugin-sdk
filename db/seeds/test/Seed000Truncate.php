<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Seed000Truncate extends AbstractSeed
{
    public function run()
    {
        $this->execute(
            "
            SET FOREIGN_KEY_CHECKS = 0;
            TRUNCATE TABLE risk_check_definitions;
            TRUNCATE TABLE merchant_user_invitations;
            TRUNCATE TABLE merchant_risk_check_settings;
            TRUNCATE TABLE score_thresholds_configuration;
            TRUNCATE TABLE merchant_settings;
            TRUNCATE TABLE merchant_onboardings;
            TRUNCATE TABLE merchants_debtors;
            TRUNCATE TABLE merchant_users;
            TRUNCATE TABLE merchant_user_roles;
            TRUNCATE TABLE merchants;
            SET FOREIGN_KEY_CHECKS = 1;
        "
        );
    }
}
