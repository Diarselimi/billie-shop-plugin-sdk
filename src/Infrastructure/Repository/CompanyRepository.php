<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Company\CompanyEntity;
use App\DomainModel\Company\CompanyRepositoryInterface;

class CompanyRepository extends AbstractRepository implements CompanyRepositoryInterface
{
    public function insert(CompanyEntity $company): void
    {
        $id = $this->doInsert('
            INSERT INTO companies
            (merchant_id, debtor_id, created_at, updated_at)
            VALUES
            (:merchant_id, :debtor_id, :created_at, :updated_at)
        ', [
            'merchant_id' => $person->getMerchantId(),
            'debtor_id' => $person->getDebtorId(),
            'created_at' => $person->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $person->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $company->setId($id);
    }

    public function getOneById(int $id):? CompanyEntity
    {
        $company = $this->doFetch('SELECT id, merchant_id, debtor_id, created_at, updated_at FROM companies WHERE id = :id', [
            'id' => $id,
        ]);

        if (!$company) {
            return null;
        }

        return (new CompanyEntity())
            ->setId($company['id'])
            ->setMerchantId($company['merchant_id'])
            ->setDebtorId($company['debtor_id'])
            ->setCreatedAt(new \DateTime($company['created_at']))
            ->setUpdatedAt(new \DateTime($company['updated_at']))
        ;
    }

    public function getOneByIdRaw(int $id):? array
    {
        $address = $this->doFetch('SELECT * FROM addresses WHERE id = :id', [
            'id' => $id,
        ]);

        return $address ?: null;
    }
}
