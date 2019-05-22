<?php

namespace App\DomainModel\MerchantDebtor;

class MerchantDebtorFinancingDetailsEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantDebtorFinancialDetailsEntity
    {
        return (new MerchantDebtorFinancialDetailsEntity())
            ->setId((int) $row['id'])
            ->setMerchantDebtorId(intval($row['merchant_debtor_id']))
            ->setFinancingLimit(floatval($row['financing_limit']))
            ->setFinancingPower(floatval($row['financing_power']))
            ->setCreatedAt(new \DateTime($row['created_at']))
        ;
    }

    public function create(
        int $merchantDebtorId,
        float $financingLimit,
        float $financingPower
    ): MerchantDebtorFinancialDetailsEntity {
        return (new MerchantDebtorFinancialDetailsEntity())
            ->setMerchantDebtorId($merchantDebtorId)
            ->setFinancingLimit($financingLimit)
            ->setFinancingPower($financingPower)
            ->setCreatedAt(new \DateTime())
        ;
    }
}
