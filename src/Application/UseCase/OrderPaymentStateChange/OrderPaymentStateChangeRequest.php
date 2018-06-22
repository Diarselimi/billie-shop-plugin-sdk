<?php

namespace App\Application\UseCase\OrderPaymentStateChange;

use App\DomainModel\Borscht\OrderPaymentDetailsDTO;

class OrderPaymentStateChangeRequest
{
    private $orderPaymentDetails;

    public function __construct(OrderPaymentDetailsDTO $orderPaymentDetails)
    {
        $this->orderPaymentDetails = $orderPaymentDetails;
    }

    public function getOrderPaymentDetails(): OrderPaymentDetailsDTO
    {
        return $this->orderPaymentDetails;
    }
}
