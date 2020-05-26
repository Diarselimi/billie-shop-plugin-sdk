<?php

namespace App\DomainModel\OrderUpdate;

use Ozean12\Money\TaxedMoney\TaxedMoney;
use App\DomainModel\Order\OrderContainer\OrderContainer;

class UpdateOrderAmountValidator
{
    public function getValidatedValue(
        OrderContainer $orderContainer,
        ?TaxedMoney $newAmount,
        array $allowedStates
    ): ?TaxedMoney {
        if (!$newAmount || !$this->isAmountChanged($orderContainer, $newAmount)) {
            return null;
        }

        $order = $orderContainer->getOrder();

        if (
            !in_array($order->getState(), $allowedStates, true)
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
