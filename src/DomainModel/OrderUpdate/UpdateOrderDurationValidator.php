<?php

namespace App\DomainModel\OrderUpdate;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;

class UpdateOrderDurationValidator
{
    /**
     * Order states allowed to change duration
     */
    private static $durationUpdateAllowedOrderStates = [
        OrderEntity::STATE_SHIPPED,
        OrderEntity::STATE_PAID_OUT,
        OrderEntity::STATE_LATE,
        OrderEntity::STATE_WAITING,
        OrderEntity::STATE_CREATED,
    ];

    public function getValidatedValue(OrderContainer $orderContainer, ?int $duration): ?int
    {
        if ($duration === null || !$this->isDurationChanged($orderContainer, $duration)) {
            return null;
        }

        $order = $orderContainer->getOrder();

        if (
            !in_array($order->getState(), self::$durationUpdateAllowedOrderStates, true)
            || !$this->isDurationAllowed($orderContainer, $duration)
        ) {
            throw new UpdateOrderException('Order duration cannot be updated');
        }

        return $duration;
    }

    private function isDurationChanged(OrderContainer $orderContainer, ?int $newDuration): bool
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        return ($newDuration) && $financialDetails->getDuration() !== $newDuration;
    }

    private function isDurationAllowed(OrderContainer $orderContainer, ?int $newDuration): bool
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        return $newDuration > $financialDetails->getDuration();
    }
}
