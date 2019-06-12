<?php

namespace App\DomainModel\Order;

interface SalesforceInterface
{
    public function pauseOrderDunning(string $orderUuid, int $numberOfDays): void;
}
