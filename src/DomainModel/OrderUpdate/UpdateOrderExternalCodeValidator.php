<?php

namespace App\DomainModel\OrderUpdate;

use App\DomainModel\Order\OrderContainer\OrderContainer;

class UpdateOrderExternalCodeValidator
{
    public function getValidatedValue(OrderContainer $orderContainer, ?string $externalCode): ?string
    {
        if (empty($externalCode)) {
            return null;
        }

        if (!empty($orderContainer->getOrder()->getExternalCode())) {
            throw new UpdateOrderException('Order external code cannot be updated');
        }

        return $externalCode;
    }
}
