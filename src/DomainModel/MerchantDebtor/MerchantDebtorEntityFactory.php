<?php

namespace App\DomainModel\MerchantDebtor;

use Ramsey\Uuid\Uuid;

class MerchantDebtorEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantDebtorEntity
    {
        return (new MerchantDebtorEntity())
            ->setId($row['id'])
            ->setMerchantId($row['merchant_id'])
            ->setDebtorId($row['debtor_id'])
            ->setUuid($row['uuid'])
            ->setPaymentDebtorId($row['payment_debtor_id'])
            ->setScoreThresholdsConfigurationId($row['score_thresholds_configuration_id'])
            ->setIsWhitelisted(boolval($row['is_whitelisted']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']));
    }

    public function create(
        string $debtorId,
        string $merchantId,
        string $paymentDebtorId,
        bool $isWhitelisted = false
    ): MerchantDebtorEntity {
        $now = new \DateTime();

        return (new MerchantDebtorEntity())
            ->setMerchantId($merchantId)
            ->setDebtorId($debtorId)
            ->setUuid(Uuid::uuid4()->toString())
            ->setPaymentDebtorId($paymentDebtorId)
            ->setIsWhitelisted($isWhitelisted)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }
}
