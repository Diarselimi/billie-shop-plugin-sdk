<?php

namespace App\Infrastructure\Borscht;

use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\Borscht\OrderPaymentDetailsDTO;

class Borscht implements BorschtInterface
{
    public function getDebtorPaymentDetails(int $debtorPaymentId): DebtorPaymentDetailsDTO
    {
        return (new DebtorPaymentDetailsDTO())
            ->setBankAccountBic('BICDEXXX')
            ->setBankAccountIban('DE112233')
        ;
    }

    public function getOrderPaymentDetails(int $orderPaymentId): OrderPaymentDetailsDTO
    {
        return (new OrderPaymentDetailsDTO())
            ->setPayoutAmount(5000)
            ->setFeeAmount(100)
            ->setFeeRate(1.5)
            ->setDueDate(new \DateTime())
        ;
    }
}
