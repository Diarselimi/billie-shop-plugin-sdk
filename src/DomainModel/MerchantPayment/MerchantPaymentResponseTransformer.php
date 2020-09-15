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
        $this->addIsAllocated($item);
        $this->addOverpaidAmount($item);

        return $item;
    }

    public function addIsAllocated(array &$item): void
    {
        $item['is_allocated'] = (bool) $item['is_allocated'];
    }

    public function addOverpaidAmount(array &$item): void
    {
        $overpayment = 0;
        $orders = [];

        if (!empty($item['orders'])) {
            $orders = array_filter($item['orders'], static function (array $order) use (&$overpayment) {
                if ($order['uuid'] !== null) {
                    return $order;
                }

                $overpayment += $order['mapped_amount'];
            });
        }

        $item['orders'] = array_values($orders);
        $item['overpaid_amount'] = $overpayment;
    }
}
