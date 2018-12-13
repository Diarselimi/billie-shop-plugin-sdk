<?php

namespace App\DomainModel\Order;

use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\RiskCheck\Checker\CheckInterface;
use App\DomainModel\RiskCheck\Checker\CheckResult;
use App\DomainModel\RiskCheck\RiskCheckEntityFactory;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class OrderChecksRunnerService implements LoggingInterface
{
    use LoggingTrait;

    private $producer;

    private $riskCheckRepository;

    private $riskCheckFactory;

    private $alfred;

    private $checkLoader;

    private $sentry;

    public function __construct(
        ProducerInterface $producer,
        RiskCheckRepositoryInterface $riskCheckRepository,
        RiskCheckEntityFactory $riskCheckFactory,
        AlfredInterface $alfred,
        ServiceLocator $checkLoader,
        \Raven_Client $sentry
    ) {
        $this->producer = $producer;
        $this->riskCheckRepository = $riskCheckRepository;
        $this->riskCheckFactory = $riskCheckFactory;
        $this->alfred = $alfred;
        $this->checkLoader = $checkLoader;
        $this->sentry = $sentry;
    }

    public function runPreconditionChecks(OrderContainer $order): bool
    {
        $amountCheckResult = $this->check($order, 'amount');
        $debtorCountryCheckResult = $this->check($order, 'debtor_country');
        $debtorIndustrySectorCheckResult = $this->check($order, 'debtor_industry_sector');

        return $amountCheckResult && $debtorCountryCheckResult && $debtorIndustrySectorCheckResult;
    }

    public function runChecks(OrderContainer $order): bool
    {
        $this->logWaypoint('debtor != merchant check');
        $debtorNotCustomerCheckResult = $this->check($order, 'debtor_not_customer');
        if (!$debtorNotCustomerCheckResult) {
            return false;
        }

        $this->logWaypoint('company name check');
        $nameCheckResult = $this->check($order, 'debtor_name');
        if (!$nameCheckResult) {
            $this->logInfo('Company name check failed');

            return false;
        }

        $this->logWaypoint('address check');
        $addressCheckResult = $this->check($order, 'debtor_address');
        if (!$addressCheckResult) {
            $this->logInfo('Address check failed');

            return false;
        }

        $this->logWaypoint('blacklist check');
        $debtorBlacklistedCheckResult = $this->check($order, 'debtor_blacklisted');
        if (!$debtorBlacklistedCheckResult) {
            return false;
        }

        $this->logWaypoint('overdue check');
        $debtorOverDueCheckResult = $this->check($order, 'debtor_overdue');
        if (!$debtorOverDueCheckResult) {
            return false;
        }

        $this->logWaypoint('debtor score check');
        $isDebtorEligible = $this->isDebtorEligible($order);
        if (!$isDebtorEligible) {
            $this->logInfo('Debtor score check failed');

            return false;
        }

        $this->logInfo('Main checks passed');

        return true;
    }

    public function publishCheckResult(CheckResult $checkResult, OrderContainer $order)
    {
        $riskCheckEntity = $this->riskCheckFactory->createFromCheckResult($checkResult, $order->getOrder()->getId());
        $this->riskCheckRepository->insert($riskCheckEntity);
        $riskCheckData = [
            'event_id' => $riskCheckEntity->getId(),
            'is_passed' => $checkResult->isPassed(),
            'name' => $checkResult->getName(),
            'attributes' => $checkResult->getAttributes(),
        ];

        try {
            $this->producer->publish(json_encode($riskCheckData), 'risk_check_result_paella');
        } catch (\ErrorException $exception) {
            $this->logError('[suppressed] Rabbit producer exception', [
                'exception' => $exception,
                'data' => $riskCheckData,
            ]);

            $this->sentry->captureException($exception);
        }
    }

    private function isDebtorEligible(OrderContainer $order): bool
    {
        $debtorId = $order->getMerchantDebtor()->getDebtorId();
        $passed = $this->alfred->isEligibleForPayAfterDelivery($debtorId);

        $checkResult = new CheckResult($passed, 'company_b2b_score', []);
        $riskCheckEntity = $this->riskCheckFactory->createFromCheckResult($checkResult, $order->getOrder()->getId());
        $this->riskCheckRepository->insert($riskCheckEntity);

        return $checkResult->isPassed();
    }

    private function check(OrderContainer $order, string $name): bool
    {
        $check = $this->getCheck($name);
        $result = $check->check($order);

        $this->logInfo('Check result: {check} -> {result}', [
            'check' => $name,
            'result' => (int) $result->isPassed(),
            'attributes' => $result->getAttributes(),
        ]);

        $this->publishCheckResult($result, $order);

        return $result->isPassed();
    }

    private function getCheck($name): CheckInterface
    {
        if (!$this->checkLoader->has($name)) {
            throw new \RuntimeException("Risk check {$name} not registered");
        }

        return $this->checkLoader->get($name);
    }
}
