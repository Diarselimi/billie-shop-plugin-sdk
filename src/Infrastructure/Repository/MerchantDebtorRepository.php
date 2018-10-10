<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;

class MerchantDebtorRepository extends AbstractRepository implements MerchantDebtorRepositoryInterface
{
    private $factory;

    public function __construct(MerchantDebtorEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantDebtorEntity $merchantDebtor): void
    {
        $id = $this->doInsert('
            INSERT INTO merchants_debtors
            (merchant_id, debtor_id, created_at, updated_at)
            VALUES
            (:merchant_id, :debtor_id, :created_at, :updated_at)
        ', [
            'merchant_id' => $merchantDebtor->getMerchantId(),
            'debtor_id' => $merchantDebtor->getDebtorId(),
            'created_at' => $merchantDebtor->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $merchantDebtor->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $merchantDebtor->setId($id);
    }

    public function getOneById(int $id): ?MerchantDebtorEntity
    {
        $company = $this->doFetchOne('
          SELECT id, merchant_id, debtor_id, created_at, updated_at 
          FROM merchants_debtors 
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        if (!$company) {
            return null;
        }

        return $this->factory->createFromDatabaseRow($company);
    }

    public function getOneByMerchantAndDebtorId(string $merchantId, string $debtorId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT id, merchant_id, debtor_id, created_at, updated_at 
          FROM merchants_debtors 
          WHERE merchant_id = :merchant_id
          AND debtor_id = :debtor_id', [
            'merchant_id' => $merchantId,
            'debtor_id' => $debtorId,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
