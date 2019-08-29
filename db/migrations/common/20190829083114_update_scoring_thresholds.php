<?php

use Phinx\Migration\AbstractMigration;

class UpdateScoringThresholds extends AbstractMigration
{
    public function change()
    {
        $this->execute('
            UPDATE score_thresholds_configuration SET crefo_low_score_threshold = 299, schufa_sole_trader_score_threshold = 316
        ');
    }
}
