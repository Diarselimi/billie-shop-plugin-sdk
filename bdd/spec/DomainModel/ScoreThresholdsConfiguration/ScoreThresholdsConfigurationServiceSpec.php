<?php

namespace spec\App\DomainModel\ScoreThresholdsConfiguration;

use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationService;
use PhpSpec\ObjectBehavior;

class ScoreThresholdsConfigurationServiceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ScoreThresholdsConfigurationService::class);
    }

    public function it_returns_debtor_score_threshold_if_exists()
    {
        $debtorScoreThresholds = (new ScoreThresholdsConfigurationEntity())
            ->setCrefoHighScoreThreshold(100);

        $score = $this->getCrefoHighScoreThreshold(null, $debtorScoreThresholds);

        $score->shouldBe(100);
    }

    public function it_returns_merchant_score_threshold_if_exists_and_debtor_score_threshold_is_null()
    {
        $merchantScoreThresholds = (new ScoreThresholdsConfigurationEntity())
            ->setCrefoHighScoreThreshold(100);

        $score = $this->getCrefoHighScoreThreshold($merchantScoreThresholds, null);

        $score->shouldBe(100);
    }
}
