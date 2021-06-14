<?php

declare(strict_types=1);

namespace App\DomainModel\OrderFinancialDetails;

use App\Support\ArrayCollection;

/**
 * @method OrderFinancialDetailsEntity[] toArray()
 * @method \ArrayIterator|OrderFinancialDetailsEntity[] getIterator()
 */
class OrderFinancialDetailsCollection extends ArrayCollection
{
    /**
     * @return OrderFinancialDetailsEntity[]
     */
    public function keySingleByOrderId(): array
    {
        return collect($this->items)
            ->reduce(
                function (array $carry, OrderFinancialDetailsEntity $item) {
                    if (isset($carry[$item->getOrderId()])) {
                        throw new \LogicException(__METHOD__ . ': key already exists');
                    }
                    $carry[$item->getOrderId()] = $item;

                    return $carry;
                },
                []
            );
    }
}
