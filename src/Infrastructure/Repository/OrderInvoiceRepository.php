<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;

class OrderInvoiceRepository extends AbstractRepository implements OrderInvoiceRepositoryInterface
{
    public function insert(OrderInvoiceEntity $orderInvoiceEntity): OrderInvoiceEntity
    {
        $id = $this->doInsert('
            INSERT INTO order_invoices
            (order_id, file_id, invoice_number, created_at)
            VALUES
            (:order_id, :file_id, :invoice_number, :created_at)
            
        ', [
            'order_id' => $orderInvoiceEntity->getOrderId(),
            'file_id' => $orderInvoiceEntity->getFileId(),
            'invoice_number' => $orderInvoiceEntity->getInvoiceNumber(),
            'created_at' => $orderInvoiceEntity->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $orderInvoiceEntity->setId($id);

        return $orderInvoiceEntity;
    }
}
