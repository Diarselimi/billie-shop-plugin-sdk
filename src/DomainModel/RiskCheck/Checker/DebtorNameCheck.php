<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;
use App\DomainModel\RiskCheck\CompanyNameComparator;

class DebtorNameCheck implements CheckInterface
{
    public const NAME = 'debtor_name';

    private $nameComparator;

    public function __construct(CompanyNameComparator $nameComparator)
    {
        $this->nameComparator = $nameComparator;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $nameFromRegistry = $order->getDebtorCompany()->getName();
        $nameFromOrder = $order->getDebtorExternalData()->getName();
        $result = $this->nameComparator->compare($nameFromOrder, $nameFromRegistry);

        return new CheckResult($result, self::NAME, [
            'name_registry' => $nameFromRegistry,
            'name_order' => $nameFromOrder,
        ]);
    }
}
