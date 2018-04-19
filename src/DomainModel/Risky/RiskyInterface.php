<?php

namespace App\DomainModel\Risky;

use App\DomainModel\Order\OrderEntity;

interface RiskyInterface
{
    const AMOUNT = 'order_amount';
    const DEBTOR_COUNTRY = 'order_debtor_country';
    const DEBTOR_INDUSTRY_SECTOR = 'order_debtor_industry_sector';

    public function runCheck(OrderEntity $order, string $name): bool;
}
