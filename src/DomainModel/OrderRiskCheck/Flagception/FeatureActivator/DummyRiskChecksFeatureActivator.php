<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Flagception\FeatureActivator;

use App\DomainModel\FeatureFlag\FeatureFlagManager;
use App\DomainModel\OrderRiskCheck\Flagception\DummyRiskCheckStrategyManager;
use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Model\Context;

final class DummyRiskChecksFeatureActivator implements FeatureActivatorInterface
{
    public const FEATURE_NAME = FeatureFlagManager::FEATURE_DUMMY_RISK_CHECKS;

    private const FEATURE_ACTIVATOR_NAME = 'dummy_risk_checks_feature_activator';

    public const CONTEXT_RISK_CHECK_NAME = 'risk_check_name';

    public const CONTEXT_ORDER_CONTAINER = 'order_container';

    private bool $isDummyChecksEnabled;

    private DummyRiskCheckStrategyManager $dummyStrategyManager;

    public function __construct(DummyRiskCheckStrategyManager $dummyStrategyManager, bool $isDummyRiskChecksFeatureEnabled)
    {
        $this->isDummyChecksEnabled = $isDummyRiskChecksFeatureEnabled;
        $this->dummyStrategyManager = $dummyStrategyManager;
    }

    public function getName()
    {
        return self::FEATURE_ACTIVATOR_NAME;
    }

    public function isActive($name, Context $context)
    {
        if (!$this->isDummyChecksEnabled) {
            return false;
        }

        if ($name !== self::FEATURE_NAME) {
            return false;
        }

        return $this->dummyStrategyManager->isActive(
            $context->get(self::CONTEXT_RISK_CHECK_NAME),
            $context->get(self::CONTEXT_ORDER_CONTAINER)
        );
    }
}
