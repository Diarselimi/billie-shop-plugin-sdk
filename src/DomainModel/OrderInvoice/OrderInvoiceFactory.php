<?php

namespace App\DomainModel\OrderInvoice;

class OrderInvoiceFactory
{
    public function create(int $orderId, int $fileId, string $invoiceNumber): OrderInvoiceEntity
    {
        return (new OrderInvoiceEntity())
            ->setOrderId($orderId)
            ->setFileId($fileId)
            ->setInvoiceNumber($invoiceNumber)
        ;
    }
}
