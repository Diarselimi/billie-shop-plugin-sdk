<?php

namespace App\DomainModel\OrderUpdate;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;

class UpdateOrderInvoiceNumberValidator
{
    /**
     * Order states allowed to change invoice data
     */
    private static $invoiceUpdateAllowedOrderStates = [
        OrderStateManager::STATE_SHIPPED,
        OrderStateManager::STATE_PAID_OUT,
        OrderStateManager::STATE_LATE,
    ];

    public function getValidatedValue(OrderContainer $orderContainer, ?string $invoiceNumber): ?string
    {
        if (!$this->isinvoiceNumberChanged($orderContainer, $invoiceNumber)) {
            return null;
        }

        $order = $orderContainer->getOrder();

        if (!in_array($order->getState(), self::$invoiceUpdateAllowedOrderStates, true)) {
            throw new UpdateOrderException('Order invoice number cannot be updated');
        }

        return $invoiceNumber;
    }

    private function isInvoiceNumberChanged(OrderContainer $orderContainer, ?string $invoiceNumber): bool
    {
        $order = $orderContainer->getOrder();

        return $invoiceNumber && $invoiceNumber !== $order->getInvoiceNumber();
    }
}
