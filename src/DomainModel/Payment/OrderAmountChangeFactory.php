<?php

namespace App\DomainModel\Payment;

class OrderAmountChangeFactory
{
    public function createFromBorschtResponse(array $response): OrderAmountChangeDTO
    {
        return (new OrderAmountChangeDTO())
            ->setId($response['id'])
            ->setType($response['type'])
            ->setAmountChange($response['amount_change'])
            ->setOutstandingAmount($response['outstanding_amount'])
            ->setPaidAmount($response['paid_amount'])
        ;
    }
}
