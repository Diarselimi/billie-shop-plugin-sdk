<?php

namespace App\DomainModel\Order;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Risky\RiskyInterface;
use App\DomainModel\Alfred\AlfredInterface;

class OrderChecksRunnerService implements LoggingInterface
{
    use LoggingTrait;

    private const OVERDUE_MAX_DAYS = 30;

    private $risky;
    private $orderRepository;
    private $alfred;

    public function __construct(RiskyInterface $risky, OrderRepositoryInterface $orderRepository, AlfredInterface $alfred)
    {
        $this->risky = $risky;
        $this->orderRepository = $orderRepository;
        $this->alfred = $alfred;
    }

    public function runPreconditionChecks(OrderContainer $order): bool
    {
        $amountCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::AMOUNT);
        $debtorCountryCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_COUNTRY);
        $debtorIndustrySectorCheck = $this->risky->runOrderCheck(
            $order->getOrder(),
            RiskyInterface::DEBTOR_INDUSTRY_SECTOR
        );

        $this->logInfo('Precondition checks: amount: {amount}, country: {country}, industry sector: {industry}', [
            'amount' => (int)$amountCheck,
            'country' => (int)$debtorCountryCheck,
            'industry' => (int)$debtorIndustrySectorCheck,
        ]);

        return $amountCheck && $debtorCountryCheck && $debtorIndustrySectorCheck;
    }

    public function runChecks(OrderContainer $order, ?string $debtorCrefoId): bool
    {
        $merchant = $order->getMerchant();
        $debtorId = $order->getMerchantDebtor()->getDebtorId();

        $this->logWaypoint('debtor != merchant check');
        $debtorNotCustomerCheck = $debtorId !== $merchant->getCompanyId();
        if (!$debtorNotCustomerCheck) {
            $this->logInfo('Debtor not customer check failed');

            return false;
        }

        $this->logWaypoint('address check');
        $addressCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_ADDRESS);
        if (!$addressCheck) {
            $this->logInfo('Address check failed');

            return false;
        }

        $this->logWaypoint('blacklist check');
        $blacklistCheck = $this->alfred->isDebtorBlacklisted($debtorId);
        if ($blacklistCheck) {
            $this->logInfo('Black list check failed');

            return false;
        }

        $this->logWaypoint('overdue check');
        $debtorOverDueCheck = $this->getMerchantDebtorOverdues($order);
        if (!$debtorOverDueCheck) {
            $this->logInfo('Debtor overdue check failed');

            return false;
        }

        $this->logWaypoint('debtor score check');
        $debtorScoreCheck = $this->risky->runDebtorScoreCheck($order, $debtorCrefoId);
        if (!$debtorScoreCheck) {
            $this->logInfo('Debtor score check failed');

            return false;
        }

        $this->logInfo('Main checks passed');

        return true;
    }

    private function getMerchantDebtorOverdues(OrderContainer $order): bool
    {
        $overdues = $this->orderRepository->getCustomerOverdues($order->getOrder()->getMerchantDebtorId());
        foreach ($overdues as $overdue) {
            if ($overdue > static::OVERDUE_MAX_DAYS) {
                $this->logInfo('Debtor overdue check failed');

                return false;
            }
        }

        return true;
    }
}
