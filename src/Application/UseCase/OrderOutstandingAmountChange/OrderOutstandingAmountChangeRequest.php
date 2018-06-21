<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\DomainModel\Borscht\OrderAmountChangeDTO;

class OrderOutstandingAmountChangeRequest
{
    private $orderAmountChangeDetails;

    public function __construct(OrderAmountChangeDTO $orderAmountChangeDetails)
    {
        $this->orderAmountChangeDetails = $orderAmountChangeDetails;
    }

    public function getOrderAmountChangeDetails(): OrderAmountChangeDTO
    {
        return $this->orderAmountChangeDetails;
    }
}
