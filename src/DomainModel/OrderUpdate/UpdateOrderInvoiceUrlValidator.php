<?php

namespace App\DomainModel\OrderUpdate;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;

class UpdateOrderInvoiceUrlValidator
{
    /**
     * Order states allowed to change invoice data
     */
    private static $invoiceUpdateAllowedOrderStates = [
        OrderStateManager::STATE_SHIPPED,
        OrderStateManager::STATE_PAID_OUT,
        OrderStateManager::STATE_LATE,
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
