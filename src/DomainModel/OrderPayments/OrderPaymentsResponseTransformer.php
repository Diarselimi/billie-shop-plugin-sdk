<?php

namespace App\DomainModel\OrderPayments;

use App\Support\PaginatedCollection;

class OrderPaymentsResponseTransformer
{
    private $filter;

    public function __construct(OrderPaymentsResponseFilter $filter)
    {
        $this->filter = $filter;
    }

    public function transformPaymentsCollection(PaginatedCollection $collection): PaginatedCollection
    {
        $collection->filter($this->filter);
        $collection->map([$this, 'mapPaymentItem']);

        return $collection;
    }

    public function mapPaymentItem(array $item): array
    {
        return (new OrderPaymentDTO())
            ->setAmount($item['mapped_amount'] ?? $item['pending_amount'])
            ->setCreatedAt($this->getPaymentCreatedAt($item))
            ->setState($this->getPaymentState($item))
            ->setType($item['payment_type'])
            ->toArray()
        ;
    }

    public function getPaymentState(array $item): string
    {
        return $item['mapped_at'] ? OrderPaymentDTO::PAYMENT_STATE_COMPLETE : OrderPaymentDTO::PAYMENT_STATE_NEW;
    }

    public function getPaymentCreatedAt(array $item): \DateTime
    {
        return $item['mapped_at'] ? new \DateTime($item['mapped_at']) : new \DateTime($item['created_at']);
    }
}
