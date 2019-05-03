<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantDebtor\MerchantDebtorIdentifierDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantDebtorRepository extends AbstractPdoRepository implements MerchantDebtorRepositoryInterface
{
    const TABLE_NAME = "merchants_debtors";

    private const SELECT_FIELDS = 'id, merchant_id, debtor_id, payment_debtor_id, financing_limit, score_thresholds_configuration_id, is_whitelisted, created_at, updated_at';

    private $factory;

    public function __construct(MerchantDebtorEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantDebtorEntity $merchantDebtor): void
    {
        $id = $this->doInsert('
            INSERT INTO '. self::TABLE_NAME .'
            (merchant_id, debtor_id, payment_debtor_id, financing_limit, score_thresholds_configuration_id, created_at, updated_at, is_whitelisted)
            VALUES
            (:merchant_id, :debtor_id, :payment_debtor_id, :financing_limit, :score_thresholds_configuration_id, :created_at, :updated_at, :is_whitelisted)
        ', [
            'merchant_id' => $merchantDebtor->getMerchantId(),
            'debtor_id' => $merchantDebtor->getDebtorId(),
            'payment_debtor_id' => $merchantDebtor->getPaymentDebtorId(),
            'financing_limit' => $merchantDebtor->getFinancingLimit(),
            'score_thresholds_configuration_id' => $merchantDebtor->getScoreThresholdsConfigurationId(),
            'created_at' => $merchantDebtor->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $merchantDebtor->getUpdatedAt()->format(self::DATE_FORMAT),
            'is_whitelisted' => (int) $merchantDebtor->isWhitelisted(),
        ]);

        $merchantDebtor->setId($id);
    }

    public function update(MerchantDebtorEntity $merchantDebtor): void
    {
        $merchantDebtor->setUpdatedAt(new \DateTime());

        $this->doUpdate('
            UPDATE '.self::TABLE_NAME.'
            SET financing_limit = :financing_limit, updated_at = :updated_at, is_whitelisted = :whitelisted
            WHERE id = :id
        ', [
            'id' => $merchantDebtor->getId(),
            'financing_limit' => $merchantDebtor->getFinancingLimit(),
            'updated_at' => $merchantDebtor->getUpdatedAt()->format(self::DATE_FORMAT),
            'whitelisted' => (int) $merchantDebtor->isWhitelisted(),
        ]);
    }

    public function getOneById(int $id): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM '.self::TABLE_NAME.'
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByMerchantAndDebtorId(string $merchantId, string $debtorId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM '.self::TABLE_NAME.'
          WHERE merchant_id = :merchant_id
          AND debtor_id = :debtor_id', [
            'merchant_id' => $merchantId,
            'debtor_id' => $debtorId,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByMerchantExternalId(string $merchantExternalId, string $merchantId, array $excludedOrderStates): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
            SELECT ' . self::SELECT_FIELDS . '
            FROM '.self::TABLE_NAME.' 
            WHERE merchants_debtors.id = (
                SELECT merchant_debtor_id
                FROM orders
                INNER JOIN debtor_external_data ON orders.debtor_external_data_id = debtor_external_data.id
                WHERE orders.merchant_id = :merchant_id
                AND debtor_external_data.merchant_external_id = :merchant_external_id
                AND merchant_debtor_id IS NOT NULL
                '.($excludedOrderStates ? 'AND orders.state NOT IN ("'.implode('", "', $excludedOrderStates).'")' : '').'
                ORDER BY orders.id DESC
                LIMIT 1
            )
        ', [
            'merchant_external_id' => $merchantExternalId,
            'merchant_id' => $merchantId,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getMerchantDebtorCreatedOrdersAmount(int $merchantDebtorId): float
    {
        $row = $this->doFetchOne('
            SELECT SUM(amount_gross) AS created_amount
            FROM orders
            WHERE orders.merchant_debtor_id = :id AND orders.state = :state_created
        ', [
            'id' => $merchantDebtorId,
            'state_created' => OrderStateManager::STATE_CREATED,
        ]);

        return $row['created_amount'] ?? 0;
    }

    public function getDebtorsWithExternalId(string $where = ''): \Generator
    {
        $where = $where ? "WHERE {$where}" : '';
        $tableName = self::TABLE_NAME;

        $sql = <<<SQL
    SELECT debtor_id, 
           merchants_debtors.merchant_id,
           merchant_external_id,
           merchant_debtor_id
    FROM orders
        INNER JOIN debtor_external_data ON (debtor_external_data_id = debtor_external_data.id)
        INNER JOIN {$tableName} ON (merchant_debtor_id = merchants_debtors.id)
    {$where}
    GROUP BY debtor_id, merchant_id, merchant_external_id, merchant_debtor_id
    ORDER BY merchant_id, merchant_external_id, debtor_id
SQL;
        $stmt = $this->doExecute($sql);
        while ($stmt && $row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield (new MerchantDebtorIdentifierDTO())
                ->setMerchantDebtorId($row['merchant_debtor_id'])
                ->setMerchantId($row['merchant_id'])
                ->setDebtorId((int) $row['debtor_id'])
                ->setMerchantExternalId($row['merchant_external_id']);
        }
    }
}
