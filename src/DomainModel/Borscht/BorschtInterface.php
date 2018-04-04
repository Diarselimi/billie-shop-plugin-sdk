<?php

namespace App\DomainModel\Borscht;

interface BorschtInterface
{
    public function getDebtorPaymentDetails(int $debtorPaymentId): DebtorPaymentDetailsDTO;
    public function getOrderPaymentDetails(int $orderPaymentId): OrderPaymentDetailsDTO;
}
