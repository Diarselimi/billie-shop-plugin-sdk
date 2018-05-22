<?php

namespace App\DomainModel\Order;

use App\DomainModel\Risky\RiskyInterface;

class OrderChecksRunnerService
{
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

        return $amountCheck && $debtorCountryCheck && $debtorIndustrySectorCheck;
    }

    public function runChecks(OrderContainer $order, ?string $debtorCrefoId): bool
    {
        $merchant = $order->getMerchant();
        $debtorId = $order->getMerchantDebtor()->getDebtorId();

        $debtorNotCustomerCheck = $debtorId !== $merchant->getCompanyId();
        if (!$debtorNotCustomerCheck) {
            return false;
        }

        $addressCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_ADDRESS);
        if (!$addressCheck) {
            return false;
        }

        $blackListCheck = true;
        if (!$blackListCheck) {
            return false;
        }

        $debtorOverDueCheck = true;
        if (!$debtorOverDueCheck) {
            return false;
        }

        $debtorScoreCheck = $this->risky->runDebtorScoreCheck($order, $debtorCrefoId);
        if (!$debtorScoreCheck) {
            return false;
        }

        return true;
    }
}
