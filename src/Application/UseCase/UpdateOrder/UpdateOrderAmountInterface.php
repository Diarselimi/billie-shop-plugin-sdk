<?php

namespace App\Application\UseCase\UpdateOrder;

use Ozean12\Money\TaxedMoney\TaxedMoney;

interface UpdateOrderAmountInterface
{
    public function getAmount(): ?TaxedMoney;
}
