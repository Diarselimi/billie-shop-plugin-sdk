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

    public function runPreconditionChecks(OrderEntity $order): bool
    {
        $amount = $this->risky->runCheck($order, RiskyInterface::AMOUNT);
        $debtorCountry = $this->risky->runCheck($order, RiskyInterface::DEBTOR_COUNTRY);
        $debtorIndustrySector = $this->risky->runCheck($order, RiskyInterface::DEBTOR_INDUSTRY_SECTOR);

        return $amount && $debtorCountry && $debtorIndustrySector;
    }
}
