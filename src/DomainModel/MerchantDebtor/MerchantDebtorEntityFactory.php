<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\Alfred\DebtorDTO;

class MerchantDebtorEntityFactory
{
    public function createFromDatabaseRow(array $row): MerchantDebtorEntity
    {
        return (new MerchantDebtorEntity())
            ->setId($row['id'])
            ->setMerchantId($row['merchant_id'])
            ->setDebtorId($row['debtor_id'])
            ->setExternalId($row['external_id'])
            ->setIsDebtorIdValid($row['debtor_id_validation'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createFromDebtorDTO(DebtorDTO $debtor, string $externalId, string $merchantId): MerchantDebtorEntity
    {
        $now = new \DateTime();

        return (new MerchantDebtorEntity())
            ->setMerchantId($merchantId)
            ->setDebtorId($debtor->getId())
            ->setExternalId($externalId)
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
        ;
    }
}
