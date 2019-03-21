<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorIndustrySectorCheck implements CheckInterface
{
    const NAME = 'debtor_industry_sector';

    private const BLACKLISTED_INDUSTRY_SECTORS = ['25.4', '92', 'T', 'U'];

    public function check(OrderContainer $order): CheckResult
    {
        $industrySector = $order->getDebtorExternalData()->getIndustrySector();
        $result = !in_array($industrySector, self::BLACKLISTED_INDUSTRY_SECTORS, true);

        return new CheckResult($result, self::NAME);
    }
}
