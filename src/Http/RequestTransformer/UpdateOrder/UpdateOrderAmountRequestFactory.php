<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer\UpdateOrder;

use App\Http\RequestTransformer\AmountRequestFactory;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\HttpFoundation\Request;

class UpdateOrderAmountRequestFactory
{
    private $amountRequestFactory;

    public function __construct(AmountRequestFactory $amountRequestFactory)
    {
        $this->amountRequestFactory = $amountRequestFactory;
    }

    public function create(Request $request): ?TaxedMoney
    {
        $amount = $request->request->get('amount');
        if (is_string($amount)) {
            $amount = json_decode($amount, true);
        }

        if (!is_array($amount)) {
            return null;
        }

        $gross = $amount['gross'] ?? null;
        $net = $amount['net'] ?? null;
        $tax = $amount['tax'] ?? null;

        if ($gross === null && $net === null && $tax === null) {
            return null;
        }

        return $this->amountRequestFactory->createFromArray($amount);
    }
}
