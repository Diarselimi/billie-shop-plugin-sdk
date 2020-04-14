<?php

namespace App\DomainModel\Order;

use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
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
        $riskCheckResults = $orderContainer->getRiskChecks();

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

    public function rerunFailedChecks(OrderContainer $orderContainer, array $riskChecksToSkip): bool
    {
        /** @var OrderRiskCheckEntity[] $failedRiskChecks */
        $failedRiskChecks = array_filter(
            $orderContainer->getRiskChecks(),
            function (OrderRiskCheckEntity $orderRiskCheck) {
                return !$orderRiskCheck->isPassed();
            }
        );

        foreach ($failedRiskChecks as $orderCheckResult) {
            if (in_array($orderCheckResult->getRiskCheckDefinition()->getName(), $riskChecksToSkip)) {
                continue;
            }

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

    public function rerunCheck(OrderContainer $orderContainer, string $checkName): bool
    {
        foreach ($orderContainer->getRiskChecks() as $orderCheckResult) {
            if ($checkName !== $orderCheckResult->getRiskCheckDefinition()->getName()) {
                continue;
            }

            $check = $this->getCheck($checkName);
            $result = $check->check($orderContainer)->isPassed();

            $orderCheckResult->setIsPassed($result);
            $this->orderRiskCheckRepository->update($orderCheckResult);

            return $result;
        }

        return false;
    }

    private function runChecks(OrderContainer $orderContainer, array $checkNames): bool
    {
        foreach ($checkNames as $checkName) {
            $checkResult = $this->check($orderContainer, $checkName);

            if (!$checkResult) {
                return false;
            }
        }

        return true;
    }

    private function check(OrderContainer $orderContainer, string $riskCheckName): bool
    {
        $this->logWaypoint(str_replace('_', ' ', $riskCheckName) . ' check');

        $merchantRiskCheckSetting = $this->merchantRiskCheckSettingsRepository->getOneByMerchantIdAndRiskCheckName(
            $orderContainer->getMerchant()->getId(),
            $riskCheckName
        );

        if (!$merchantRiskCheckSetting || !$merchantRiskCheckSetting->isEnabled()) {
            return true;
        }

        $check = $this->getCheck($riskCheckName);
        $result = $check->check($orderContainer);

        $this->logInfo('Check result: {check} -> {result}', [
            'check' => $riskCheckName,
            'result' => (int) $result->isPassed(),
        ]);

        $this->persistCheckResult($result, $orderContainer);

        if (!$merchantRiskCheckSetting->isDeclineOnFailure() && !$result->isPassed()) {
            return true;
        }

        return $result->isPassed();
    }

    private function persistCheckResult(CheckResult $checkResult, OrderContainer $orderContainer): void
    {
        $riskCheckEntity = $this->riskCheckFactory->createFromCheckResult($checkResult, $orderContainer->getOrder()->getId());
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
