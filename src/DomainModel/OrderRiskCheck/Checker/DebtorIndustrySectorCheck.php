<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;

class DebtorIndustrySectorCheck implements CheckInterface
{
    const NAME = 'debtor_industry_sector';

    private const BLACKLISTED_INDUSTRY_SECTORS = ['25.4', '92', 'T', 'U'];

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $industrySector = $orderContainer->getDebtorExternalData()->getIndustrySector();

        if (!$industrySector) {
            return new CheckResult(true, self::NAME);
        }

        return new CheckResult(!in_array($industrySector, self::BLACKLISTED_INDUSTRY_SECTORS, true), self::NAME);
    }
}
