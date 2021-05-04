<?php

declare(strict_types=1);

namespace App\Support;

use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Symfony\Component\HttpFoundation\Request;

class TaxedMoneyFactoryDecorator extends TaxedMoneyFactory
{
    public static function createFromRequest(Request $request): ?TaxedMoney
    {
        $money = $request->request->get('amount');
        if ($money === null) {
            return null;
        }

        return new TaxedMoney(new Money($money['gross']), new Money($money['net']), new Money($money['tax']));
    }
}
