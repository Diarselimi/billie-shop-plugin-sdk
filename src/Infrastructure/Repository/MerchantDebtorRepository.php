<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorIdentifierDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantDebtorRepository extends AbstractPdoRepository implements MerchantDebtorRepositoryInterface
{
    public const TABLE_NAME = "merchants_debtors";

    private const SELECT_FIELDS = 'id, merchant_id, debtor_id, payment_debtor_id, uuid, score_thresholds_configuration_id, is_whitelisted, created_at, updated_at';

    private $factory;

    public function __construct(MerchantDebtorEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantDebtorEntity $merchantDebtor): void
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . '
            (merchant_id, debtor_id, payment_debtor_id, uuid, score_thresholds_configuration_id, created_at, updated_at, is_whitelisted)
            VALUES
            (:merchant_id, :debtor_id, :payment_debtor_id, :uuid, :score_thresholds_configuration_id, :created_at, :updated_at, :is_whitelisted)
        ', [
            'merchant_id' => $merchantDebtor->getMerchantId(),
            'debtor_id' => $merchantDebtor->getDebtorId(),
            'payment_debtor_id' => $merchantDebtor->getPaymentDebtorId(),
            'uuid' => $merchantDebtor->getUuid(),
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
            UPDATE ' . self::TABLE_NAME . '
            SET is_whitelisted = :whitelisted, updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $merchantDebtor->getId(),
            'whitelisted' => (int) $merchantDebtor->isWhitelisted(),
            'updated_at' => $merchantDebtor->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);
    }

    public function getOneById(int $id): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM ' . self::TABLE_NAME . '
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByUuidAndMerchantId(string $uuid, int $merchantId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM ' . self::TABLE_NAME . '
          WHERE uuid = :uuid AND merchant_id = :merchant_id
        ', [
            'uuid' => $uuid,
            'merchant_id' => $merchantId,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByMerchantAndDebtorId(string $merchantId, string $debtorId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM ' . self::TABLE_NAME . '
          WHERE merchant_id = :merchant_id
          AND debtor_id = :debtor_id', [
            'merchant_id' => $merchantId,
            'debtor_id' => $debtorId,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByExternalIdAndMerchantId(string $merchantExternalId, string $merchantId, array $excludedOrderStates): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
            SELECT ' . self::SELECT_FIELDS . '
            FROM ' . self::TABLE_NAME . ' 
            WHERE merchants_debtors.id = (
                SELECT merchant_debtor_id
                FROM orders
                INNER JOIN debtor_external_data ON orders.debtor_external_data_id = debtor_external_data.id
                WHERE orders.merchant_id = :merchant_id
                AND debtor_external_data.merchant_external_id = :merchant_external_id
                AND merchant_debtor_id IS NOT NULL
                ' . ($excludedOrderStates ? 'AND orders.state NOT IN ("' . implode('", "', $excludedOrderStates) . '")' : '') . '
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
        return $this->getMerchantDebtorOrdersAmountByState($merchantDebtorId, OrderStateManager::STATE_CREATED);
    }

    public function getMerchantDebtorOrdersAmountByState(int $merchantDebtorId, string $state): float
    {
        $row = $this->doFetchOne('
            SELECT SUM(amount_gross) AS amount FROM order_financial_details
            INNER JOIN orders ON orders.id = order_financial_details.order_id
            WHERE orders.merchant_debtor_id = :id AND orders.state = :state
        ', [
            'id' => $merchantDebtorId,
            'state' => $state,
        ]);

        return $row['amount'] ?? 0;
    }

    public function getMerchantDebtorIdentifierDtos(string $where = ''): ?\Generator
    {
        $where = $where ? "WHERE {$where}" : '';
        $tableName = self::TABLE_NAME;

        $sql = <<<SQL
    SELECT {$tableName}.uuid as merchant_debtor_uuid, debtor_id, 
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
        $count = 0;

        while ($stmt && $row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield (new MerchantDebtorIdentifierDTO())
                ->setMerchantDebtorUuid($row['merchant_debtor_uuid'])
                ->setMerchantDebtorId($row['merchant_debtor_id'])
                ->setMerchantDebtorId($row['merchant_debtor_id'])
                ->setMerchantId($row['merchant_id'])
                ->setDebtorId((int) $row['debtor_id'])
                ->setMerchantExternalId($row['merchant_external_id']);
            $count++;
        }

        if ($count === 0) {
            yield from [];
        }
    }

    public function getOneMerchantDebtorIdentifierDto(int $merchantDebtorId): ?MerchantDebtorIdentifierDTO
    {
        return $this->getMerchantDebtorIdentifierDtos(self::TABLE_NAME . ".id={$merchantDebtorId}")->current();
    }

    public function findExternalId(int $merchantDebtorId): ?string
    {
        $identifierDto = $this->getOneMerchantDebtorIdentifierDto($merchantDebtorId);

        return $identifierDto ? $identifierDto->getMerchantExternalId() : null;
    }

    public function getByMerchantId(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection,
        ?string $searchString
    ): array {
        $tableName = self::TABLE_NAME;
        $where = $tableName . '.merchant_id = :merchant_id';
        $queryParameters = ['merchant_id' => $merchantId];
        $select = "merchants_debtors.id AS id, debtor_id, debtor_external_data.merchant_external_id AS external_id";

        $sql = <<<SQL
    SELECT %s
    FROM {$tableName}
        INNER JOIN (
	        SELECT MAX(id) AS id, merchant_debtor_id FROM orders GROUP BY merchant_debtor_id
        ) AS last_order ON last_order.merchant_debtor_id = merchants_debtors.id
        INNER JOIN orders ON orders.id = last_order.id
        INNER JOIN debtor_external_data ON debtor_external_data.id = orders.debtor_external_data_id
    WHERE %s
SQL;

        if ($searchString) {
            $where .= ' AND (debtor_external_data.merchant_external_id LIKE :search)';
            $queryParameters['search'] = '%'.$searchString.'%';
        }

        $totalCount = $this->doFetchOne('SELECT count(*) as total_count FROM (' . sprintf(
            $sql,
            'merchants_debtors.id',
            $where
        ) . ') AS md', $queryParameters);

        $sql .= " ORDER BY :sort_field :sort_direction LIMIT {$offset},{$limit}";
        $queryParameters['sort_field'] = $tableName . '.' . $sortBy;
        $queryParameters['sort_direction'] = $sortDirection;

        $rows = $this->doFetchAll(sprintf($sql, $select, $where), $queryParameters);

        return [
            'total' => $totalCount['total_count'] ?? 0,
            'rows' => $rows,
        ];
    }
}
