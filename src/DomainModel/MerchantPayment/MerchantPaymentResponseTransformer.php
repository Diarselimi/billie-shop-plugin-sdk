<?php

namespace App\DomainModel\MerchantPayment;

use App\Support\PaginatedCollection;

class MerchantPaymentResponseTransformer
{
    public function expandPaymentsCollection(PaginatedCollection $collection): PaginatedCollection
    {
        $collection->map([$this, 'expandPaymentItem']);

        return $collection;
    }

    public function expandPaymentItem(array $item): array
    {
        $item = $this->addIsAllocated($item);
        $item = $this->addOverpaidAmount($item);

        $item['merchant_debtor_uuid'] = $item['merchant_debtor']['uuid'] ?? null;
        unset($item['merchant_debtor']);

        return $item;
    }

    public function addIsAllocated(array $item): array
    {
        if (array_key_exists('is_allocated', $item)) {
            $item['is_allocated'] = (bool) $item['is_allocated'];
        }

        return $item;
    }

    public function addOverpaidAmount(array $item): array
    {
        $overpayment = 0;
        $orders = array_filter($item['orders'], static function (array $order) use (&$overpayment) {
            if ($order['uuid'] !== null) {
                return $order;
            }

            $overpayment = $order['mapped_amount'];
        });

        $item['orders'] = $orders;
        $item['overpaid_amount'] = $overpayment;

        return $item;
    }
}
