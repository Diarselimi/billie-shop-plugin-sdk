<?php

namespace App\DomainModel\Order;

use App\DomainModel\Invoice\Invoice;

interface SalesforceInterface
{
    public function pauseOrderDunning(string $orderUuid, int $numberOfDays): void;

    public function getOrderDunningStatus(string $orderUuid): ? string;

    public function getOrderCollectionsStatus(string $orderUuid): ?string;

    public function isDunningInProgress(Invoice $invoice): bool;
}
