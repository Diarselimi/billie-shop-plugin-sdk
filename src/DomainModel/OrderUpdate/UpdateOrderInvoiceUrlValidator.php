<?php

namespace App\DomainModel\OrderUpdate;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;

class UpdateOrderInvoiceUrlValidator
{
    /**
     * Order states allowed to change invoice data
     */
    private static $invoiceUpdateAllowedOrderStates = [
        OrderEntity::STATE_SHIPPED,
        OrderEntity::STATE_PAID_OUT,
        OrderEntity::STATE_LATE,
    ];

    public function getValidatedValue(OrderContainer $orderContainer, ?string $invoiceUrl): ?string
    {
        if (!$this->isInvoiceUrlChanged($orderContainer, $invoiceUrl)) {
            return null;
        }

        $order = $orderContainer->getOrder();

        if (!in_array($order->getState(), self::$invoiceUpdateAllowedOrderStates, true)) {
            throw new UpdateOrderException('Order invoice URL cannot be updated');
        }

        return $invoiceUrl;
    }

    private function isInvoiceUrlChanged(OrderContainer $orderContainer, ?string $invoiceUrl): bool
    {
        $order = $orderContainer->getOrder();

        return $invoiceUrl && $invoiceUrl !== $order->getInvoiceUrl();
    }
}
