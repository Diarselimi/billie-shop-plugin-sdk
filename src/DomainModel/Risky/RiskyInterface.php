<?php

namespace App\DomainModel\Risky;

use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;

interface RiskyInterface
{
    const AMOUNT = 'order_amount';
    const DEBTOR_COUNTRY = 'order_debtor_country';
    const DEBTOR_INDUSTRY_SECTOR = 'order_debtor_industry_sector';
    const DEBTOR_ADDRESS = 'order_debtor_address';
    const DEBTOR_SCORE = 'company_b2b_score';

    public function runOrderCheck(OrderEntity $order, string $name): bool;
    public function runDebtorScoreCheck(OrderContainer $orderContainer, ?string $crefoId): bool;
}
