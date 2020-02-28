<?php

namespace App\DomainModel\OrderPayments;

use App\Support\PaginationFilterInterface;

class OrderPaymentsResponseFilter implements PaginationFilterInterface
{
    public function check(array $item): bool
    {
        return $item['mapped_at'] !== null || $item['payment_type'] !== OrderPaymentDTO::PAYMENT_TYPE_INVOICE_PAYBACK;
    }
}
