<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;

class DebtorNotCustomerCheck implements CheckInterface
{
    const NAME = 'debtor_not_customer';

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $debtorId = $orderContainer->getMerchantDebtor()->getDebtorId();
        $merchantId = $orderContainer->getMerchant()->getCompanyId();
        $result = $debtorId !== $merchantId;

        return new CheckResult($result, self::NAME);
    }
}
