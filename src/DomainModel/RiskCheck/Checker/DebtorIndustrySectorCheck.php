<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorIndustrySectorCheck implements CheckInterface
{
    private const NAME = 'debtor_industry_sector';
    private const ACCEPTED_INDUSTRY_SECTORS = ['25.4', '92', 'T', 'U'];

    public function check(OrderContainer $order): CheckResult
    {
        $industrySector = $order->getDebtorExternalData()->getIndustrySector();
        $result = !in_array($industrySector, self::ACCEPTED_INDUSTRY_SECTORS, true);

        return new CheckResult($result, self::NAME, [
            'industry_sector' => $industrySector,
        ]);
    }
}
