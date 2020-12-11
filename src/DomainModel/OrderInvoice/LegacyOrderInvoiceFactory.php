<?php

namespace App\DomainModel\OrderInvoice;

class LegacyOrderInvoiceFactory
{
    public function create(int $orderId, int $fileId, string $invoiceNumber): LegacyOrderInvoiceEntity
    {
        return (new LegacyOrderInvoiceEntity())
            ->setOrderId($orderId)
            ->setFileId($fileId)
            ->setInvoiceNumber($invoiceNumber)
        ;
    }
}
