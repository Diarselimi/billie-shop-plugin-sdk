<?php

namespace App\DomainModel\Order;

use App\DomainEvent\OrderRiskCheck\RiskCheckResultEvent;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\CheckInterface;
use App\DomainModel\OrderRiskCheck\CheckResult;
use App\DomainModel\OrderRiskCheck\Flagception\FeatureActivator\DummyRiskChecksFeatureActivator;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntityFactory;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Flagception\Manager\FeatureManagerInterface;
use Flagception\Model\Context;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OrderChecksRunnerService implements LoggingInterface
{
    use LoggingTrait;

    private $orderRiskCheckRepository;

    private $riskCheckFactory;

    private $checkLoader;

    private $merchantRiskCheckSettingsRepository;

    private $preIdentificationChecks;

    private $postIdentificationChecks;

    private $dispatcher;

    private $featureManager;

    public function __construct(
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        OrderRiskCheckEntityFactory $riskCheckFactory,
        ServiceLocator $checkLoader,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository,
        EventDispatcherInterface $dispatcher,
        array $preIdentificationChecks,
        array $postIdentificationChecks,
        FeatureManagerInterface $featureManager
    ) {
        $this->orderRiskCheckRepository = $orderRiskCheckRepository;
        $this->riskCheckFactory = $riskCheckFactory;
        $this->checkLoader = $checkLoader;
        $this->merchantRiskCheckSettingsRepository = $merchantRiskCheckSettingsRepository;
        $this->preIdentificationChecks = $preIdentificationChecks;
        $this->postIdentificationChecks = $postIdentificationChecks;
        $this->dispatcher = $dispatcher;
        $this->featureManager = $featureManager;
    }

    public function passesPreIdentificationChecks(OrderContainer $orderContainer): bool
    {
        return $this->runChecks($orderContainer, $this->preIdentificationChecks);
    }

    public function passesPostIdentificationChecks(OrderContainer $orderContainer): bool
    {
        return $this->runChecks($orderContainer, $this->postIdentificationChecks);
    }

    public function hasFailedSoftDeclinableChecks(OrderContainer $orderContainer): bool
    {
        return $orderContainer->getRiskCheckResultCollection()->getFirstSoftDeclined() !== null;
    }

    public function rerunFailedChecks(OrderContainer $orderContainer, array $riskChecksToSkip): bool
    {
        $failedRiskChecksNames = [];

        $checkResultCollection = $orderContainer->getRiskCheckResultCollection();
        foreach ($checkResultCollection->getAllDeclined() as $checkResult) {
            if (in_array($checkResult->getName(), $riskChecksToSkip)) {
                continue;
            }

            $failedRiskChecksNames[] = $checkResult->getName();
        }

        return $this->rerunChecks($orderContainer, $failedRiskChecksNames);
    }

    public function rerunChecks(OrderContainer $orderContainer, array $checkNames): bool
    {
        foreach ($checkNames as $checkName) {
            $checkResult = $this->check($orderContainer, $checkName);

            if (!$checkResult->isPassed()) {
                return false;
            }
        }

        return true;
    }

    private function runChecks(OrderContainer $orderContainer, array $checkNames): bool
    {
        foreach ($checkNames as $checkName) {
            $checkResult = $this->check($orderContainer, $checkName);

            if (!$checkResult->isPassed() && $checkResult->isDeclineOnFailure()) {
                return false;
            }
        }

        return true;
    }

    private function check(
        OrderContainer $orderContainer,
        string $riskCheckName
    ): CheckResult {
        $this->logWaypoint(str_replace('_', ' ', $riskCheckName) . ' check');

        $merchantRiskCheckSetting = $this->merchantRiskCheckSettingsRepository->getOneByMerchantIdAndRiskCheckName(
            $orderContainer->getMerchant()->getId(),
            $riskCheckName
        );

        if (!$merchantRiskCheckSetting || !$merchantRiskCheckSetting->isEnabled()) {
            return (new CheckResult(true, $riskCheckName))->setDeclineOnFailure(false);
        }

        $check = $this->getCheck($orderContainer, $riskCheckName);
        $result = $check->check($orderContainer)
            ->setDeclineOnFailure($merchantRiskCheckSetting->isDeclineOnFailure());

        $this->dispatcher->dispatch(new RiskCheckResultEvent($orderContainer, $result));

        $this->logInfo('Check result: {name} -> {number}', [
            LoggingInterface::KEY_NAME => $riskCheckName,
            LoggingInterface::KEY_NUMBER => (int) $result->isPassed(),
        ]);

        $this->persistCheckResult($result, $orderContainer);

        return $result;
    }

    private function persistCheckResult(CheckResult $checkResult, OrderContainer $orderContainer): void
    {
        $riskCheckEntity = $this->riskCheckFactory->createFromCheckResult(
            $checkResult,
            $orderContainer->getOrder()->getId()
        );
        $this->orderRiskCheckRepository->insert($riskCheckEntity);
    }

    private function getCheck(OrderContainer $orderContainer, $name): CheckInterface
    {
        // wrap it into the factory
        $context = new Context();
        $context->add(DummyRiskChecksFeatureActivator::ORDER_CONTAINER, $orderContainer);
        $context->add(DummyRiskChecksFeatureActivator::RISK_CHECK_NAME, $name);

        if ($this->featureManager->isActive(DummyRiskChecksFeatureActivator::FEATURE_ACTIVATOR_NAME, $context)) {
            $name .= '_dummy';
        }

        if (!$this->checkLoader->has($name)) {
            throw new \RuntimeException("Risk check {$name} not registered");
        }

        return $this->checkLoader->get($name);
    }
}
