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
        $item = $this->addIsOverpaid($item);

        // simplify
        $item['merchant_debtor_uuid'] = $item['merchant_debtor']['uuid'] ?? null;
        unset($item['merchant_debtor']);

        return $item;
    }

    public function addIsAllocated(array $item): array
    {
        if (array_key_exists('is_allocated', $item)) {
            $item['is_allocated'] = boolval($item['is_allocated']);
        }

        return $item;
    }

    public function addIsOverpaid(array $item): array
    {
        if (!array_key_exists('orders', $item) || empty($item['orders'])) {
            return $item;
        }

        $ordersAmountTotal = 0;
        foreach ($item['orders'] as $order) {
            $ordersAmountTotal += ($order['amount'] ?? 0);
        }
        $item['is_overpaid'] = ($item['amount'] > $ordersAmountTotal);

        return $item;
    }
}
