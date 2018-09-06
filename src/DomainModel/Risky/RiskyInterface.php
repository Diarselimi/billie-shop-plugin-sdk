<?php

namespace App\DomainModel\Risky;

use App\DomainModel\Order\OrderContainer;
use App\Infrastructure\Risky\RiskyResultDTO;

interface RiskyInterface
{
    public function runDebtorScoreCheck(OrderContainer $orderContainer, string $companyName, ?string $crefoId): RiskyResultDTO;
}
