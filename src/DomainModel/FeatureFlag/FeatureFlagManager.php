<?php

declare(strict_types=1);

namespace App\DomainModel\FeatureFlag;

use Flagception\Manager\FeatureManagerInterface;
use Flagception\Model\Context;

class FeatureFlagManager
{
    public const FEATURE_DUMMY_RISK_CHECKS = 'dummy_risk_checks';

    private FeatureManagerInterface $featureManager;

    private array $overrides = [];

    public function __construct(FeatureManagerInterface $featureManager)
    {
        $this->featureManager = $featureManager;
    }

    public function isEnabled(string $featureName, array $contextData = []): bool
    {
        if (isset($this->overrides[$featureName])) {
            return $this->overrides[$featureName];
        }

        $context = new Context();
        foreach ($contextData as $key => $value) {
            $context->add($key, $value);
        }

        return $this->featureManager->isActive($featureName, $context);
    }

    public function overrideIsEnabled(string $featureName, bool $enabled): void
    {
        $this->overrides[$featureName] = $enabled;
    }
}
