<?php

namespace App\DomainModel\OrderFinancialDetails;

use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;

class OrderFinancialDetailsFactory
{
    public function create(
        int $orderId,
        TaxedMoney $amount,
        int $duration
    ): OrderFinancialDetailsEntity {
        return (new OrderFinancialDetailsEntity)
            ->setOrderId($orderId)
            ->setAmountGross($amount->getGross())
            ->setAmountNet($amount->getNet())
            ->setAmountTax($amount->getTax())
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
