<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorNotCustomerCheck implements CheckInterface
{
    const NAME = 'debtor_not_customer';

    public function check(OrderContainer $order): CheckResult
    {
        $debtorId = $order->getMerchantDebtor()->getDebtorId();
        $merchantId = $order->getMerchant()->getCompanyId();
        $result = $debtorId !== $merchantId;

        return new CheckResult($result, self::NAME);
    }
}
