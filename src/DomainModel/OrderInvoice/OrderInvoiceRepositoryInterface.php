<?php

namespace App\DomainModel\OrderInvoice;

interface OrderInvoiceRepositoryInterface
{
    public function insert(OrderInvoiceEntity $orderInvoiceEntity): OrderInvoiceEntity;

    /**
     * @param  int                        $orderId
     * @return array|OrderInvoiceEntity[]
     */
    public function findByOrderId(int $orderId): array;

    public function getByUuidAndMerchant(string $invoiceUuid, int $merchantId): ?OrderInvoiceEntity;
}
