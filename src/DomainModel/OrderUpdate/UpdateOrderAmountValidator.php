<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
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
        OrderStateManager::STATE_PRE_APPROVED,
        OrderStateManager::STATE_WAITING,
        OrderStateManager::STATE_CREATED,
    ];

    public function getValidatedValue(OrderContainer $orderContainer, ?CreateOrderAmountRequest $newAmount): ?CreateOrderAmountRequest
    {
        if (!$newAmount || $this->isAmountEmpty($newAmount) || !$this->isAmountChanged($orderContainer, $newAmount)) {
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

    private function isAmountChanged(OrderContainer $orderContainer, CreateOrderAmountRequest $newAmount): bool
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        return
            ($financialDetails->getAmountGross() !== $newAmount->getGross())
            || ($financialDetails->getAmountNet() !== $newAmount->getNet())
            || ($financialDetails->getAmountTax() !== $newAmount->getTax());
    }

    private function isAmountEmpty(CreateOrderAmountRequest $amount): bool
    {
        return
            $amount->getGross() === null
            && $amount->getNet() === null
            && $amount->getTax() === null
        ;
    }

    private function isAmountAllowed(OrderContainer $orderContainer, CreateOrderAmountRequest $newAmount): bool
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        return
            ($newAmount->getGross() <= $financialDetails->getAmountGross())
            && ($newAmount->getNet() <= $financialDetails->getAmountNet())
            && ($newAmount->getTax() <= $financialDetails->getAmountTax());
    }
}
