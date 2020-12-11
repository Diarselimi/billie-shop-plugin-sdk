<?php

namespace App\DomainModel\OrderInvoice;

use App\Support\AbstractFactory;

class OrderInvoiceFactory extends AbstractFactory
{
    public function create(int $orderId, string $invoiceUuid): OrderInvoiceEntity
    {
        return (new OrderInvoiceEntity())
            ->setOrderId($orderId)
            ->setInvoiceUuid($invoiceUuid)
        ;
    }

    public function createFromArray(array $data): OrderInvoiceEntity
    {
        return (new OrderInvoiceEntity())
            ->setId($data['id'])
            ->setOrderId($data['order_id'])
            ->setInvoiceUuid($data['invoice_uuid'])
            ->setCreatedAt(new \DateTime($data['created_at']))
        ;
    }
}
