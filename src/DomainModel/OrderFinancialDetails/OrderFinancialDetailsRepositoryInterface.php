<?php

namespace App\DomainModel\OrderFinancialDetails;

interface OrderFinancialDetailsRepositoryInterface
{
    public function insert(OrderFinancialDetailsEntity $orderFinancialDetailsEntity): void;

    public function getLatestByOrderId(int $orderId): ? OrderFinancialDetailsEntity;

    /**
     * @param  int[]                           $orderIds
     * @return OrderFinancialDetailsCollection
     */
    public function getLatestByOrderIds(array $orderIds): OrderFinancialDetailsCollection;

    public function getLatestByOrderUuid(string $orderUuid): ?OrderFinancialDetailsEntity;
}
