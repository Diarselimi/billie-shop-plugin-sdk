<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;

class DebtorCountryCheck implements CheckInterface
{
    const NAME = 'debtor_country';

    private const ACCEPTED_COUNTRIES = ['DE'];

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $country = $orderContainer->getDebtorExternalDataAddress()->getCountry();
        $result = in_array($country, self::ACCEPTED_COUNTRIES, true);

        return new CheckResult($result, self::NAME);
    }
}
