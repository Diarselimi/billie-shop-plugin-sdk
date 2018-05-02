<?php

namespace App\DomainModel\Company;

use App\DomainModel\Alfred\DebtorDTO;

class CompanyEntityFactory
{
    public function createFromDatabaseRow(array $row): CompanyEntity
    {
        return (new CompanyEntity())
            ->setId($row['id'])
            ->setMerchantId($row['merchant_id'])
            ->setDebtorId($row['debtor_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createFromDebtorDTO(DebtorDTO $debtor, string $merchantId)
    {
        return (new CompanyEntity())
            ->setMerchantId($merchantId)
            ->setDebtorId($debtor->getId())
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
    }
}
