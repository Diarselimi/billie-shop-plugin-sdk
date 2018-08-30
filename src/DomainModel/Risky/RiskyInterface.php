<?php

namespace App\DomainModel\Risky;

use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;

interface RiskyInterface
{
    public function runOrderCheck(OrderEntity $order, string $name): bool;

    public function runDebtorScoreCheck(OrderContainer $orderContainer, bool $isIdentifiedByPerson, ?string $crefoId): bool;
}
