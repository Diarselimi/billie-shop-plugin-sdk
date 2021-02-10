<?php

namespace App\Application\UseCase\LegacyUpdateOrder;

use Ozean12\Money\TaxedMoney\TaxedMoney;

interface UpdateOrderAmountInterface
{
    public function getAmount(): ?TaxedMoney;
}
