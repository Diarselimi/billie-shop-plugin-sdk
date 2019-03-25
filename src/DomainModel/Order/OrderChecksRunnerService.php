<?php

namespace App\DomainModel\Order;

use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\OrderRiskCheck\Checker\CheckInterface;
use App\DomainModel\OrderRiskCheck\Checker\CheckResult;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntityFactory;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\DependencyInjection\ServiceLocator;

class OrderChecksRunnerService implements LoggingInterface
{
    use LoggingTrait;

    private $orderRiskCheckRepository;

    private $riskCheckFactory;

    private $checkLoader;

    private $merchantRiskCheckSettingsRepository;

    private $preIdentificationChecks;

    private $postIdentificationChecks;

    public function __construct(
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        OrderRiskCheckEntityFactory $riskCheckFactory,
        ServiceLocator $checkLoader,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository,
        array $preIdentificationChecks,
        array $postIdentificationChecks
    ) {
        $this->orderRiskCheckRepository = $orderRiskCheckRepository;
        $this->riskCheckFactory = $riskCheckFactory;
        $this->checkLoader = $checkLoader;
        $this->merchantRiskCheckSettingsRepository = $merchantRiskCheckSettingsRepository;
        $this->preIdentificationChecks = $preIdentificationChecks;
        $this->postIdentificationChecks = $postIdentificationChecks;
    }

    public function runPreIdentificationChecks(OrderContainer $order): bool
    {
        return $this->runChecks($order, $this->preIdentificationChecks);
    }

    public function runPostIdentificationChecks(OrderContainer $order): bool
    {
        return $this->runChecks($order, $this->postIdentificationChecks);
    }

    public function checkForFailedSoftDeclinableCheckResults(OrderContainer $orderContainer): bool
    {
        $riskCheckResults = $this->orderRiskCheckRepository->findByOrder($orderContainer->getOrder()->getId());

        foreach ($riskCheckResults as $riskCheckResult) {
            $merchantRiskCheckSetting = $this->merchantRiskCheckSettingsRepository->getOneByMerchantIdAndRiskCheckName(
                $orderContainer->getMerchant()->getId(),
                $riskCheckResult->getRiskCheckDefinition()->getName()
            );

            if (!$riskCheckResult->isPassed() && !$merchantRiskCheckSetting->isDeclineOnFailure()) {
                return true;
            }
        }

        return false;
    }

    public function rerunFailedChecks(OrderContainer $orderContainer): bool
    {
        /** @var OrderRiskCheckEntity[] $failedRiskChecks */
        $failedRiskChecks = array_filter(
            $this->orderRiskCheckRepository->findByOrder($orderContainer->getOrder()->getId()),
            function (OrderRiskCheckEntity $orderRiskCheck) {
                return !$orderRiskCheck->isPassed();
            }
        );

        foreach ($failedRiskChecks as $orderCheckResult) {
            $check = $this->getCheck($orderCheckResult->getRiskCheckDefinition()->getName());
            $result = $check->check($orderContainer);

            if (!$result->isPassed()) {
                return false;
            }

            $orderCheckResult->setIsPassed(true);
            $this->orderRiskCheckRepository->update($orderCheckResult);
        }

        return true;
    }

    private function runChecks(OrderContainer $order, array $checkNames): bool
    {
        foreach ($checkNames as $checkName) {
            $checkResult = $this->check($order, $checkName);

            if (!$checkResult) {
                return false;
            }
        }

        return true;
    }

    private function check(OrderContainer $order, string $riskCheckName): bool
    {
        $this->logWaypoint(str_replace('_', ' ', $riskCheckName) . ' check');

        $merchantRiskCheckSetting = $this->merchantRiskCheckSettingsRepository->getOneByMerchantIdAndRiskCheckName(
            $order->getMerchant()->getId(),
            $riskCheckName
        );

        if (!$merchantRiskCheckSetting || !$merchantRiskCheckSetting->isEnabled()) {
            return true;
        }

        $check = $this->getCheck($riskCheckName);
        $result = $check->check($order);

        $this->logInfo('Check result: {check} -> {result}', [
            'check' => $riskCheckName,
            'result' => (int) $result->isPassed(),
        ]);

        $this->persistCheckResult($result, $order);

        if (!$merchantRiskCheckSetting->isDeclineOnFailure() && !$result->isPassed()) {
            return true;
        }

        return $result->isPassed();
    }

    private function persistCheckResult(CheckResult $checkResult, OrderContainer $order): void
    {
        $riskCheckEntity = $this->riskCheckFactory->createFromCheckResult($checkResult, $order->getOrder()->getId());
        $this->orderRiskCheckRepository->insert($riskCheckEntity);
    }

    private function getCheck($name): CheckInterface
    {
        if (!$this->checkLoader->has($name)) {
            throw new \RuntimeException("Risk check {$name} not registered");
        }

        return $this->checkLoader->get($name);
    }
}
