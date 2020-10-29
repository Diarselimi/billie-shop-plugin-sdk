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
            ->setUnshippedAmountGross(new Money())
            ->setUnshippedAmountNet(new Money())
            ->setUnshippedAmountTax(new Money())
            ->setDuration($duration)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
    }

    public function createFromDatabaseRow(array $row): OrderFinancialDetailsEntity
    {
        return (new OrderFinancialDetailsEntity)
            ->setId((int) $row['id'])
            ->setOrderId((int) $row['order_id'])
            ->setAmountGross(new Money((float) $row['amount_gross']))
            ->setAmountNet(new Money((float) $row['amount_net']))
            ->setAmountTax(new Money((float) $row['amount_tax']))
            ->setDuration((int) $row['duration'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
            ->setUnshippedAmountGross(new Money((float) ($row['unshipped_amount_gross'] ?? 0)))
            ->setUnshippedAmountNet(new Money((float) ($row['unshipped_amount_net'] ?? 0)))
            ->setUnshippedAmountTax(new Money((float) ($row['unshipped_amount_tax'] ?? 0)))
        ;
    }
}
