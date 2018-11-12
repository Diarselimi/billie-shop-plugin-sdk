<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

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
            (merchant_id, debtor_id, payment_debtor_id, created_at, updated_at)
            VALUES
            (:merchant_id, :debtor_id, :payment_debtor_id, :created_at, :updated_at)
        ', [
            'merchant_id' => $merchantDebtor->getMerchantId(),
            'debtor_id' => $merchantDebtor->getDebtorId(),
            'payment_debtor_id' => $merchantDebtor->getPaymentDebtorId(),
            'created_at' => $merchantDebtor->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $merchantDebtor->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $merchantDebtor->setId($id);
    }

    public function getOneById(int $id): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT id, merchant_id, debtor_id, payment_debtor_id, created_at, updated_at 
          FROM merchants_debtors 
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByMerchantAndDebtorId(string $merchantId, string $debtorId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT id, merchant_id, debtor_id, payment_debtor_id, created_at, updated_at 
          FROM merchants_debtors 
          WHERE merchant_id = :merchant_id
          AND debtor_id = :debtor_id', [
            'merchant_id' => $merchantId,
            'debtor_id' => $debtorId,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByMerchantExternalId(string $merchantExternalId, string $merchantId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
            SELECT * FROM merchants_debtors
            WHERE merchants_debtors.id = (
                SELECT merchant_debtor_id
                FROM orders
                INNER JOIN debtor_external_data ON orders.debtor_external_data_id = debtor_external_data.id
                WHERE orders.state NOT IN (:state_new, :state_declined)
                AND orders.merchant_id = :merchant_id
                AND debtor_external_data.merchant_external_id = :merchant_external_id
                ORDER BY orders.id DESC
                LIMIT 1
            )
        ', [
            'state_new' => OrderStateManager::STATE_NEW,
            'state_declined' => OrderStateManager::STATE_DECLINED,
            'merchant_external_id' => $merchantExternalId,
            'merchant_id' => $merchantId,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
