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

    public function addOverpaidAmount(array $item): array
    {
        if (!array_key_exists('orders', $item) || empty($item['orders'])) {
            return $item;
        }

        $ordersAmountTotal = 0;
        foreach ($item['orders'] as $order) {
            $ordersAmountTotal += ($order['amount'] ?? 0);
        }
        $paidAmount = $item['amount'];
        $item['overpaid_amount'] = (float) bcsub($paidAmount, $ordersAmountTotal, 2);

        return $item;
    }
}
