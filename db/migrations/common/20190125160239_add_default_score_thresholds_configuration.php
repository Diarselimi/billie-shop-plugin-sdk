<?php

use Phinx\Migration\AbstractMigration;

class AddDefaultScoreThresholdsConfiguration extends AbstractMigration
{
    public function up()
    {
        $merchantSettings = $this->fetchAll('SELECT * FROM merchant_settings WHERE score_thresholds_configuration_id IS NULL');

        foreach ($merchantSettings as $merchantSetting) {
            $merchantSettingId = $merchantSetting['id'];

            $this->table('score_thresholds_configuration')
                ->insert([
                    'crefo_low_score_threshold' => 300,
                    'crefo_high_score_threshold' => 360,
                    'schufa_low_score_threshold' => 270,
                    'schufa_average_score_threshold' => 293,
                    'schufa_high_score_threshold' => 360,
                    'schufa_sole_trader_score_threshold' => 317,
                    'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                    'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                ])
                ->save()
            ;

            $id = $this->getAdapter()->getConnection()->lastInsertId();

            $this->execute("UPDATE merchant_settings SET score_thresholds_configuration_id = $id WHERE id = $merchantSettingId");
        }
    }
}
