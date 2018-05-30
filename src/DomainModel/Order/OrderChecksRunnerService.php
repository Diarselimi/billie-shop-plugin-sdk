<?php

namespace App\DomainModel\Order;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Risky\RiskyInterface;

class OrderChecksRunnerService implements LoggingInterface
{
    use LoggingTrait;

    private $risky;

    public function __construct(RiskyInterface $risky)
    {
        $this->risky = $risky;
    }

    public function runPreconditionChecks(OrderContainer $order): bool
    {
        $amountCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::AMOUNT);
        $debtorCountryCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_COUNTRY);
        $debtorIndustrySectorCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_INDUSTRY_SECTOR);

        $this->logInfo('Precondition checks: amount: {amount}, country: {country}, industry sector: {industry}', [
            'amount' => (int) $amountCheck,
            'country' => (int) $debtorCountryCheck,
            'industry' => (int) $debtorIndustrySectorCheck,
        ]);

        return $amountCheck && $debtorCountryCheck && $debtorIndustrySectorCheck;
    }

    public function runChecks(OrderContainer $order, ?string $debtorCrefoId): bool
    {
        $merchant = $order->getMerchant();
        $debtorId = $order->getMerchantDebtor()->getDebtorId();

        $debtorNotCustomerCheck = $debtorId !== $merchant->getCompanyId();
        if (!$debtorNotCustomerCheck) {
            $this->logInfo('Debtor not customer check failed');

            return false;
        }

        $addressCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_ADDRESS);
        if (!$addressCheck) {
            $this->logInfo('Address check failed');

            return false;
        }

        $blackListCheck = true;
        if (!$blackListCheck) {
            $this->logInfo('Black list check failed');

            return false;
        }

        $debtorOverDueCheck = true;
        if (!$debtorOverDueCheck) {
            $this->logInfo('Debtor overdue check failed');

            return false;
        }

        $debtorScoreCheck = $this->risky->runDebtorScoreCheck($order, $debtorCrefoId);
        if (!$debtorScoreCheck) {
            $this->logInfo('Debtor score check failed');

            return false;
        }

        $this->logInfo('Main checks passed');

        return true;
    }
}
