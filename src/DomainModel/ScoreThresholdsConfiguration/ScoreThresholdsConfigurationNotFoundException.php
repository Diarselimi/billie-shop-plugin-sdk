<?php

declare(strict_types=1);

namespace App\DomainModel\ScoreThresholdsConfiguration;

class ScoreThresholdsConfigurationNotFoundException extends \RuntimeException
{
    protected $message = 'Score Thresholds configuration not found.';
}
