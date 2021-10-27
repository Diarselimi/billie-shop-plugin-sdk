<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\Order\OrderEntityFactory;
use App\Support\AbstractFactory;

class OrderInvoiceFactory extends AbstractFactory
{
    private OrderEntityFactory $orderEntityFactory;

    public function __construct(OrderEntityFactory $orderEntityFactory)
    {
        $this->orderEntityFactory = $orderEntityFactory;
    }

    public function create(int $orderId, string $invoiceUuid): OrderInvoiceEntity
    {
        return (new OrderInvoiceEntity())
            ->setOrderId($orderId)
            ->setInvoiceUuid($invoiceUuid);
    }

    public function createFromArray(array $data): OrderInvoiceEntity
    {
        if (key_exists('order_uuid', $data)) {
            $order = $this->orderEntityFactory->create(
                $this->sanitizeArrayKeys($data)
            );
        }

        return (new OrderInvoiceEntity())
            ->setId($data['id'])
            ->setOrderId($data['order_id'])
            ->setOrder($order ?? null)
            ->setInvoiceUuid($data['invoice_uuid'])
            ->setCreatedAt(new \DateTime($data['created_at']));
    }

    private function sanitizeArrayKeys(array $data): array
    {
        $orderData = [];
        foreach ($data as $key => $value) {
            if (stripos($key, 'order_') !== false) {
                $orderData[str_replace('order_', '', $key)] = $value;
            }
        }

        return $orderData;
    }
}
