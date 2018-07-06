<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorCountryCheck implements CheckInterface
{
    private const NAME = 'debtor_country';
    private const ACCEPTED_COUNTRIES = ['DE'];

    public function check(OrderContainer $order): CheckResult
    {
        $country = $order->getDebtorExternalDataAddress()->getCountry();
        $result = in_array($country, self::ACCEPTED_COUNTRIES, true);

        return new CheckResult($result, self::NAME, [
            'country' => $country,
        ]);
    }
}
