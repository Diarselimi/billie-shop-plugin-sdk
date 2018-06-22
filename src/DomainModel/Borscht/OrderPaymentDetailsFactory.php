<?php

namespace App\DomainModel\Borscht;

class OrderPaymentDetailsFactory
{
    public function createFromBorschtResponse(array $response): OrderPaymentDetailsDTO
    {
        return (new OrderPaymentDetailsDTO())
            ->setId($response['id'])
            ->setState($response['state'])
            ->setPayoutAmount($response['payout_amount'])
            ->setOutstandingAmount($response['outstanding_amount'])
            ->setFeeAmount($response['fee_amount'])
            ->setFeeRate($response['fee_rate'])
            ->setDueDate(new \DateTime($response['due_date']))
        ;
    }
}
