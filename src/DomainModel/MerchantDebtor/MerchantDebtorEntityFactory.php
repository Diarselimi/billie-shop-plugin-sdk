<?php

namespace App\DomainModel\MerchantDebtor;

class MerchantDebtorEntityFactory
{
    private const DEFAULT_FINANCING_LIMIT = 7500; //TODO: Move default limit to Merchant table in DB

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

    public function create(string $debtorId, string $merchantId, string $paymentDebtorId): MerchantDebtorEntity
    {
        $now = new \DateTime();

        return (new MerchantDebtorEntity())
            ->setMerchantId($merchantId)
            ->setDebtorId($debtorId)
            ->setPaymentDebtorId($paymentDebtorId)
            ->setFinancingLimit(self::DEFAULT_FINANCING_LIMIT)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }
}
