<?php

namespace App\DomainModel\OrderFinancialDetails;

use Ozean12\Money\Money;

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
            ->setAmountGross(new Money($amountGross))
            ->setAmountNet(new Money($amountNet))
            ->setAmountTax(new Money($amountTax))
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
            ->setAmountGross(new Money(floatval($row['amount_gross'])))
            ->setAmountNet(new Money(floatval($row['amount_net'])))
            ->setAmountTax(new Money(floatval($row['amount_tax'])))
            ->setDuration(intval($row['duration']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
