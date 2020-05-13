<?php

namespace App\DomainModel\OrderUpdate;

use Ozean12\Money\TaxedMoney\TaxedMoney;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;

class UpdateOrderAmountValidator
{
    /**
     * Order states allowed to change amount
     */
    private static $amountUpdateAllowedOrderStates = [
        OrderStateManager::STATE_SHIPPED,
        OrderStateManager::STATE_PAID_OUT,
        OrderStateManager::STATE_LATE,
        OrderStateManager::STATE_WAITING,
        OrderStateManager::STATE_CREATED,
    ];

    public function getValidatedValue(OrderContainer $orderContainer, ?TaxedMoney $newAmount): ?TaxedMoney
    {
        if (!$newAmount || !$this->isAmountChanged($orderContainer, $newAmount)) {
            return null;
        }

        $order = $orderContainer->getOrder();

        if (
            !in_array($order->getState(), self::$amountUpdateAllowedOrderStates, true)
            || !$this->isAmountAllowed($orderContainer, $newAmount)
        ) {
            throw new UpdateOrderException('Order amount cannot be updated');
        }

        return $newAmount;
    }

    private function isAmountChanged(OrderContainer $orderContainer, TaxedMoney $newAmount): bool
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        return
            !$financialDetails->getAmountGross()->equals($newAmount->getGross()) ||
            !$financialDetails->getAmountNet()->equals($newAmount->getNet()) ||
            !$financialDetails->getAmountTax()->equals($newAmount->getTax());
    }

    private function isAmountAllowed(OrderContainer $orderContainer, TaxedMoney $newAmount): bool
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        return
            $financialDetails->getAmountGross()->greaterThanOrEqual($newAmount->getGross()) &&
            $financialDetails->getAmountNet()->greaterThanOrEqual($newAmount->getNet()) &&
            $financialDetails->getAmountTax()->greaterThanOrEqual($newAmount->getTax());
    }
}
