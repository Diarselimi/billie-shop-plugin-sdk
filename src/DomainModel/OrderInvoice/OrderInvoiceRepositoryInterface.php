<?php

namespace App\DomainModel\OrderInvoice;

interface OrderInvoiceRepositoryInterface
{
    public function insert(OrderInvoiceEntity $orderInvoiceEntity): OrderInvoiceEntity;

    /**
     * @return array|OrderInvoiceEntity[]
     */
    public function findByOrderId(int $orderId): array;
}
