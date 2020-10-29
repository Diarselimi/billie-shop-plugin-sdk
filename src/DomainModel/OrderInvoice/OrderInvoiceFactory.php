<?php

namespace App\DomainModel\OrderInvoice;

class OrderInvoiceFactory
{
    public function create(int $orderId, int $fileId, string $invoiceNumber): OrderInvoiceEntity
    {
        // TODO: call invoice-butler before calling this method and pass the invoiceUuid to it
        return (new OrderInvoiceEntity())
            ->setOrderId($orderId)
            ->setFileId($fileId)
            ->setInvoiceNumber($invoiceNumber)
        ;
    }
}
