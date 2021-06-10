<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Aggregate;

use App\Support\ArrayCollection;

/**
 * @method OrderAggregate[] toArray()
 */
class OrderAggregateCollection extends ArrayCollection
{
    public function getIds(): array
    {
        return array_map(
            static fn (OrderAggregate $aggregate) => $aggregate->getOrder()->getId(),
            $this->items
        );
    }

    /**
     * @return OrderAggregate[]
     */
    public function keyByOrderId(): array
    {
        return collect($this->items)
            ->reduce(
                function (array $carry, OrderAggregate $item) {
                    $orderId = $item->getOrder()->getId();
                    if ($orderId === null) {
                        throw new \LogicException('Order ID is null and cannot be used as a key.');
                    }
                    $carry[$orderId] = $item;

                    return $carry;
                },
                []
            );
    }
}
