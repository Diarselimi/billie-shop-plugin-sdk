<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\Invoice\InvoiceCollection;

interface OrderInvoiceRepositoryInterface
{
    public function insert(OrderInvoiceEntity $orderInvoiceEntity): OrderInvoiceEntity;

    public function findByOrderId(int $orderId): OrderInvoiceCollection;

    public function findByOrderIds(array $orderIds): OrderInvoiceCollection;

    public function getByUuidAndMerchant(string $invoiceUuid, int $merchantId): ?OrderInvoiceEntity;

    public function getByUuid(string $invoiceUuid): ?OrderInvoiceEntity;

    public function getByInvoiceCollection(InvoiceCollection $invoiceCollection): OrderInvoiceCollection;
}
