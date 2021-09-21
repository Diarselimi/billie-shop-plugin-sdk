<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer;

use App\Support\NullableTaxedMoney;
use App\Support\NullableTaxedMoneyFactory;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Symfony\Component\HttpFoundation\Request;

class AmountRequestFactory
{
    public function create(Request $request): TaxedMoney
    {
        $amountData = $request->request->get('amount');

        return $this->createFromArray(is_array($amountData) ? $amountData : []);
    }

    public function createFromArray(array $amountData): TaxedMoney
    {
        return TaxedMoneyFactory::create(
            $amountData['gross'] ?? null,
            $amountData['net'] ?? null,
            $amountData['tax'] ?? null
        );
    }

    public function createNullableFromArray(array $amountData): NullableTaxedMoney
    {
        return NullableTaxedMoneyFactory::create(
            $amountData['gross'] ?? null,
            $amountData['net'] ?? null,
            $amountData['tax'] ?? null
        );
    }
}
