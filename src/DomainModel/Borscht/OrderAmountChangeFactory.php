<?php

namespace App\DomainModel\Borscht;

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
