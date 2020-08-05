<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Flagception\FeatureActivator;

use App\DomainModel\OrderRiskCheck\Flagception\DummyRiskCheckStrategyManager;
use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Model\Context;

final class DummyRiskChecksFeatureActivator implements FeatureActivatorInterface
{
    public const RISK_CHECK_NAME = 'risk_check_name';

    public const ORDER_CONTAINER = 'order_container';

    public const FEATURE_ACTIVATOR_NAME = 'dummy_risk_checks';

    private const DUMMY_RISK_CHECKS_FEATURE_ACTIVATOR = 'dummy_risk_checks_feature_activator';

    private $isDummyChecksEnabled;

    private $dummyStrategyManager;

    public function __construct(DummyRiskCheckStrategyManager $dummyStrategyManager, bool $isDummyChecksEnabled)
    {
        $this->isDummyChecksEnabled = $isDummyChecksEnabled;
        $this->dummyStrategyManager = $dummyStrategyManager;
    }

    public function getName()
    {
        return self::DUMMY_RISK_CHECKS_FEATURE_ACTIVATOR;
    }

    public function isActive($name, Context $context)
    {
        if (!$this->isDummyChecksEnabled) {
            return false;
        }

        if ($name !== self::FEATURE_ACTIVATOR_NAME) {
            return false;
        }

        return $this->dummyStrategyManager->isActive(
            $context->get(self::RISK_CHECK_NAME),
            $context->get(self::ORDER_CONTAINER)
        );
    }
}
