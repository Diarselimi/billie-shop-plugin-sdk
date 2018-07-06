<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorNameCheck implements CheckInterface
{
    public const NAME = 'debtor_name';
    private const MAX_DISTANCE_DEBTOR_NAME = 3;

    public function check(OrderContainer $order): CheckResult
    {
        $nameFromRegistry = $order->getDebtorCompany()->getName();
        $nameFromOrder = $order->getDebtorExternalData()->getName();
        $distance = levenshtein(strtolower($nameFromRegistry), strtolower($nameFromOrder));

        return new CheckResult($distance <= self::MAX_DISTANCE_DEBTOR_NAME, self::NAME, [
            'name_registry' => $nameFromRegistry,
            'name_order' => $nameFromOrder,
            'distance' => $distance,
        ]);
    }
}
