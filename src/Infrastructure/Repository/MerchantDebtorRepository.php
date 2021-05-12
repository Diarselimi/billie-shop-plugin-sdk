<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorIdentifierDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantDebtorRepository extends AbstractPdoRepository implements MerchantDebtorRepositoryInterface
{
    public const TABLE_NAME = "merchants_debtors";

    private const SELECT_FIELDS = [
        'id',
        'merchant_id',
        'debtor_id',
        'company_uuid',
        'payment_debtor_id',
        'uuid',
        'score_thresholds_configuration_id',
        'created_at',
        'updated_at',
    ];

    private MerchantDebtorEntityFactory $factory;

    public function __construct(MerchantDebtorEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantDebtorEntity $merchantDebtor): void
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . '
            (merchant_id, debtor_id, company_uuid, payment_debtor_id, uuid, score_thresholds_configuration_id, created_at, updated_at)
            VALUES
            (:merchant_id, :debtor_id, :company_uuid, :payment_debtor_id, :uuid, :score_thresholds_configuration_id, :created_at, :updated_at)
        ', [
            'merchant_id' => $merchantDebtor->getMerchantId(),
            'debtor_id' => $merchantDebtor->getDebtorId(),
            'company_uuid' => $merchantDebtor->getCompanyUuid(),
            'payment_debtor_id' => $merchantDebtor->getPaymentDebtorId(),
            'uuid' => $merchantDebtor->getUuid(),
            'score_thresholds_configuration_id' => $merchantDebtor->getScoreThresholdsConfigurationId(),
            'created_at' => $merchantDebtor->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $merchantDebtor->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $merchantDebtor->setId($id);
    }

    public function getOneById(int $id): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . implode(',', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getManyByDebtorCompanyId(int $debtorCompanyId): array
    {
        $results = [];

        $stmt = $this->doExecute('
          SELECT ' . implode(',', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE debtor_id = :debtor_id
        ', [
            'debtor_id' => $debtorCompanyId,
        ]);

        while ($stmt && $row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = $this->factory->createFromArray($row);
        }

        return $results;
    }

    public function getOneByUuid(string $uuid): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . implode(',', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE uuid = :uuid
        ', [
            'uuid' => $uuid,
        ]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByUuidAndMerchantId(string $uuid, int $merchantId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . implode(',', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE uuid = :uuid AND merchant_id = :merchant_id
        ', [
            'uuid' => $uuid,
            'merchant_id' => $merchantId,
        ]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByMerchantAndCompanyUuid(string $merchantId, string $companyUuid): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . implode(',', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE merchant_id = :merchant_id
          AND company_uuid = :company_uuid', [
            'merchant_id' => $merchantId,
            'company_uuid' => $companyUuid,
        ]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByExternalIdAndMerchantId(string $merchantExternalId, string $merchantId, array $excludedOrderStates = []): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
            SELECT ' . implode(',', self::SELECT_FIELDS) . '
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
            ) AND merchant_id = :merchant_id
        ', [
            'merchant_external_id' => $merchantExternalId,
            'merchant_id' => $merchantId,
        ]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByUuidOrExternalIdAndMerchantId(string $uuidOrExternalID, int $merchantId): ?MerchantDebtorEntity
    {
        $row = $this->doFetchOne('
            SELECT ' . implode(',', self::SELECT_FIELDS) . '
            FROM ' . self::TABLE_NAME . ' 
            WHERE (merchants_debtors.id = (
                SELECT merchant_debtor_id
                FROM orders
                INNER JOIN debtor_external_data ON orders.debtor_external_data_id = debtor_external_data.id
                WHERE orders.merchant_id = :merchant_id
                AND debtor_external_data.merchant_external_id = :merchant_external_id
                AND merchant_debtor_id IS NOT NULL
                ORDER BY orders.id DESC
                LIMIT 1
            ) OR uuid = :uuid) AND merchant_id = :merchant_id
        ', [
            'merchant_external_id' => $uuidOrExternalID,
            'uuid' => $uuidOrExternalID,
            'merchant_id' => $merchantId,
        ]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getMerchantDebtorCreatedOrdersAmount(int $merchantDebtorId): float
    {
        return $this->getMerchantDebtorOrdersAmountByState($merchantDebtorId, OrderEntity::STATE_CREATED);
    }

    public function getMerchantDebtorOrdersAmountByState(int $merchantDebtorId, string $state): float
    {
        $row = $this->doFetchOne('
            SELECT SUM(amount_gross) as amount FROM order_financial_details
            WHERE id IN (
                SELECT MAX(ofd.id)
                FROM order_financial_details ofd
                INNER JOIN orders ON orders.id = ofd.order_id
                WHERE orders.merchant_debtor_id = :id AND orders.state = :state
                GROUP BY order_id
            );
        ', [
            'id' => $merchantDebtorId,
            'state' => $state,
        ]);

        return (float) ($row['amount'] ?? 0);
    }

    private function getMerchantDebtorIdentifierDtos(string $where = ''): ?\Generator
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
    ORDER BY orders.id DESC
SQL;
        $stmt = $this->doExecute($sql);
        $count = 0;

        while ($stmt && $row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield (new MerchantDebtorIdentifierDTO())
                ->setMerchantDebtorUuid($row['merchant_debtor_uuid'])
                ->setMerchantDebtorId((int) $row['merchant_debtor_id'])
                ->setMerchantId((int) $row['merchant_id'])
                ->setDebtorId((int) $row['debtor_id'])
                ->setMerchantExternalId($row['merchant_external_id']);
            $count++;
        }

        if ($count === 0) {
            yield from [];
        }
    }

    private function getOneMerchantDebtorIdentifierDto(int $merchantDebtorId): ?MerchantDebtorIdentifierDTO
    {
        return $this->getMerchantDebtorIdentifierDtos(self::TABLE_NAME . ".id={$merchantDebtorId}")->current();
    }

    public function findExternalId(int $merchantDebtorId): ?string
    {
        $identifierDto = $this->getOneMerchantDebtorIdentifierDto($merchantDebtorId);

        return $identifierDto ? $identifierDto->getMerchantExternalId() : null;
    }
}
