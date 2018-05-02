<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Company\CompanyEntity;
use App\DomainModel\Company\CompanyEntityFactory;
use App\DomainModel\Company\CompanyRepositoryInterface;

class CompanyRepository extends AbstractRepository implements CompanyRepositoryInterface
{
    private $factory;

    public function __construct(CompanyEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(CompanyEntity $company): void
    {
        $id = $this->doInsert('
            INSERT INTO companies
            (merchant_id, debtor_id, created_at, updated_at)
            VALUES
            (:merchant_id, :debtor_id, :created_at, :updated_at)
        ', [
            'merchant_id' => $company->getMerchantId(),
            'debtor_id' => $company->getDebtorId(),
            'created_at' => $company->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $company->getUpdatedAt()->format('Y-m-d H:i:s'),
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

        return $this->factory->createFromDatabaseRow($company);
    }

    public function getOneByMerchantId(string $merchantId):? CompanyEntity
    {
        $company = $this->doFetch('SELECT id, merchant_id, debtor_id, created_at, updated_at FROM companies WHERE merchant_id = :merchant_id', [
            'merchant_id' => $merchantId,
        ]);

        if (!$company) {
            return null;
        }

        return $this->factory->createFromDatabaseRow($company);
    }
}
