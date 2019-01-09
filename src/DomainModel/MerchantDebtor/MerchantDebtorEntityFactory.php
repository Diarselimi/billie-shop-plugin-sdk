<?php

namespace App\DomainModel\MerchantDebtor;

class MerchantDebtorEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantDebtorEntity
    {
        return (new MerchantDebtorEntity())
            ->setId($row['id'])
            ->setMerchantId($row['merchant_id'])
            ->setDebtorId($row['debtor_id'])
            ->setPaymentDebtorId($row['payment_debtor_id'])
            ->setFinancingLimit($row['financing_limit'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
            ;
    }

    public function create(
        string $debtorId,
        string $merchantId,
        string $paymentDebtorId,
        float $financingLimit
    ): MerchantDebtorEntity {
        $now = new \DateTime();

        return (new MerchantDebtorEntity())
            ->setMerchantId($merchantId)
            ->setDebtorId($debtorId)
            ->setPaymentDebtorId($paymentDebtorId)
            ->setFinancingLimit($financingLimit)
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
            ;
    }
}
