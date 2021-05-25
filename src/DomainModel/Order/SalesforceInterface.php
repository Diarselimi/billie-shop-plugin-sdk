<?php

namespace App\DomainModel\Order;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Salesforce\PauseDunningRequestBuilder;

interface SalesforceInterface
{
    public function pauseDunning(PauseDunningRequestBuilder $requestBuilder): void;

    public function getOrderDunningStatus(string $orderUuid): ? string;

    public function getOrderCollectionsStatus(string $orderUuid): ?string;

    public function isDunningInProgress(Invoice $invoice): bool;
}
