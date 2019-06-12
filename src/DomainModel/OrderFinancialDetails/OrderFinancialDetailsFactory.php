<?php

namespace App\DomainModel\OrderFinancialDetails;

class OrderFinancialDetailsFactory
{
    public function create(
        int $orderId,
        float $amountGross,
        float $amountNet,
        float $amountTax,
        int $duration
    ): OrderFinancialDetailsEntity {
        return (new OrderFinancialDetailsEntity)
            ->setOrderId($orderId)
            ->setAmountGross($amountGross)
            ->setAmountNet($amountNet)
            ->setAmountTax($amountTax)
            ->setDuration($duration)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
    }

    public function createFromDatabaseRow(array $row): OrderFinancialDetailsEntity
    {
        return (new OrderFinancialDetailsEntity)
            ->setId(intval($row['id']))
            ->setOrderId(intval($row['order_id']))
            ->setAmountGross(floatval($row['amount_gross']))
            ->setAmountNet(floatval($row['amount_net']))
            ->setAmountTax(floatval($row['amount_tax']))
            ->setDuration(intval($row['duration']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
