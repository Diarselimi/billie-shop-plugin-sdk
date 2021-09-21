<?php

declare(strict_types=1);

namespace App\Support;

use Ozean12\Money\DecimalInterface;
use Ozean12\Money\Money;

class NullableTaxedMoneyFactory
{
    /**
     * @param DecimalInterface|int|float|string $gross
     * @param DecimalInterface|int|float|string $net
     * @param DecimalInterface|int|float|string $tax
     */
    public static function create($gross, $net, $tax): NullableTaxedMoney
    {
        return new NullableTaxedMoney(
            $gross instanceof Money ? $gross : new Money($gross),
            $net instanceof Money ? $net : new Money($net),
            $tax instanceof Money ? $tax : new Money($tax)
        );
    }
}
