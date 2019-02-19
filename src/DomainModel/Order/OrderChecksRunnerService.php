<?php

namespace App\DomainModel\Order;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\RiskCheck\Checker\CheckInterface;
use App\DomainModel\RiskCheck\Checker\CheckResult;
use App\DomainModel\RiskCheck\Checker\DebtorAddressHouseMatchCheck;
use App\DomainModel\RiskCheck\Checker\DebtorAddressPostalCodeMatchCheck;
use App\DomainModel\RiskCheck\Checker\DebtorAddressStreetMatchCheck;
use App\DomainModel\RiskCheck\Checker\DebtorScoreCheck;
use App\DomainModel\RiskCheck\RiskCheckEntityFactory;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class OrderChecksRunnerService implements LoggingInterface
{
    use LoggingTrait;

    private $riskCheckRepository;

    private $riskCheckFactory;

    private $checkLoader;

    public function __construct(
        RiskCheckRepositoryInterface $riskCheckRepository,
        RiskCheckEntityFactory $riskCheckFactory,
        ServiceLocator $checkLoader
    ) {
        $this->riskCheckRepository = $riskCheckRepository;
        $this->riskCheckFactory = $riskCheckFactory;
        $this->checkLoader = $checkLoader;
    }

    public function runPreconditionChecks(OrderContainer $order): bool
    {
        $availableFinancingLimitCheckResult = $this->check($order, 'available_financing_limit');
        $amountCheckResult = $this->check($order, 'amount');
        $debtorCountryCheckResult = $this->check($order, 'debtor_country');
        $debtorIndustrySectorCheckResult = $this->check($order, 'debtor_industry_sector');

        return
            $availableFinancingLimitCheckResult &&
            $amountCheckResult &&
            $debtorCountryCheckResult &&
            $debtorIndustrySectorCheckResult
        ;
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
        $streetMatchCheckResult = $this->check($order, DebtorAddressStreetMatchCheck::NAME);
        $houseMatchCheckResult = $this->check($order, DebtorAddressHouseMatchCheck::NAME);
        $postalCodeMatchCheckResult = $this->check($order, DebtorAddressPostalCodeMatchCheck::NAME);
        if (!$streetMatchCheckResult || !$houseMatchCheckResult || !$postalCodeMatchCheckResult) {
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
        $debtorScoreCheckResult = $this->check($order, DebtorScoreCheck::NAME);
        if (!$debtorScoreCheckResult) {
            $this->logInfo('debtor score check did not pass');

            return false;
        }

        $this->logInfo('Main checks passed');

        return true;
    }

    public function persistCheckResult(CheckResult $checkResult, OrderContainer $order)
    {
        $riskCheckEntity = $this->riskCheckFactory->createFromCheckResult($checkResult, $order->getOrder()->getId());
        $this->riskCheckRepository->insert($riskCheckEntity);
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

        $this->persistCheckResult($result, $order);

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
