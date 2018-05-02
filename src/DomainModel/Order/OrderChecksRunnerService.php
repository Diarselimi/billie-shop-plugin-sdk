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
        $amount = $this->risky->runCheck($order->getOrder(), RiskyInterface::AMOUNT);
        $debtorCountry = $this->risky->runCheck($order->getOrder(), RiskyInterface::DEBTOR_COUNTRY);
        $debtorIndustrySector = $this->risky->runCheck($order->getOrder(), RiskyInterface::DEBTOR_INDUSTRY_SECTOR);

        return $amount && $debtorCountry && $debtorIndustrySector;
    }

    public function runChecks(OrderContainer $order): bool
    {
        return true;
    }
}
