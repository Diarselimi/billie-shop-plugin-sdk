<?php

namespace App\DomainModel\OrderPayments;

use App\Support\PaginationFilterInterface;

/**
 * @deprecated we won't filter them anymore by type, in invoice payments response
 */
class OrderPaymentsResponseFilter implements PaginationFilterInterface
{
    public function check(array $item): bool
    {
        return $item['mapped_at'] !== null || $item['payment_type'] !== OrderPaymentDTO::PAYMENT_TYPE_INVOICE_PAYBACK;
    }
}
