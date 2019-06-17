<?php

namespace App\DomainModel\OrderFinancialDetails;

interface OrderFinancialDetailsRepositoryInterface
{
    public function insert(OrderFinancialDetailsEntity $orderFinancialDetailsEntity): void;

    public function getCurrentByOrderId(int $orderId): ? OrderFinancialDetailsEntity;
}
