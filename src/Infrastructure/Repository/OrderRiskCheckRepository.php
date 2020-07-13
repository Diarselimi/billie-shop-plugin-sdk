<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderRiskCheck\CheckResultCollection;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntityFactory;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderRiskCheckRepository extends AbstractPdoRepository implements OrderRiskCheckRepositoryInterface
{
    private const TABLE_NAME = 'order_risk_checks';

    private const TABLE_FIELDS = [
        'id',
        'order_id',
        'risk_check_definition_id',
        'is_passed',
        'created_at',
        'updated_at',
    ];

    private $factory;

    public function __construct(OrderRiskCheckEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(OrderRiskCheckEntity $riskCheck): void
    {
        $id = $this->doInsert('
            INSERT INTO order_risk_checks
            (order_id, risk_check_definition_id, is_passed, created_at, updated_at)
            VALUES
            (:order_id, :risk_check_definition_id, :is_passed, :created_at, :updated_at)
        ', [
            'order_id' => $riskCheck->getOrderId(),
            'risk_check_definition_id' => $riskCheck->getRiskCheckDefinition()->getId(),
            'is_passed' => (int) $riskCheck->isPassed(),
            'created_at' => $riskCheck->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $riskCheck->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $riskCheck->setId($id);
    }

    public function findByOrderAndCheckName(int $orderId, string $checkName): ?OrderRiskCheckEntity
    {
        $row = $this->doFetchOne(
            $this->generateSelectQuery(self::TABLE_NAME, $this->getFields()) .
            ' INNER JOIN risk_check_definitions ON risk_check_definitions.id = order_risk_checks.risk_check_definition_id ' .
            ' WHERE order_id = :order_id AND risk_check_definitions.name = :check_name ORDER BY order_risk_checks.id DESC',
            ['order_id' => $orderId, 'check_name' => $checkName]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function findLastFailedRiskChecksByOrderId(int $orderId): CheckResultCollection
    {
        $rows = $this->fetchAllLatestRiskChecksByOrderId($orderId);

        return $this->factory->createCheckResultFromRows($rows);
    }

    private function fetchAllLatestRiskChecksByOrderId(int $orderId)
    {
        return $this->doFetchAll(
            $this->generateSelectQuery(
                self::TABLE_NAME,
                $this->getFields(['settings.decline_on_failure', 'def.name as check_name'])
            ) .
            ' INNER JOIN merchant_risk_check_settings settings ON settings.risk_check_definition_id = ' . self::TABLE_NAME . '.risk_check_definition_id ' .
            ' INNER JOIN risk_check_definitions def ON def.id = order_risk_checks.risk_check_definition_id ' .
            ' WHERE ' . self::TABLE_NAME . '.id IN (select MAX(id) as id from order_risk_checks  where order_id = :order_id GROUP BY risk_check_definition_id ORDER BY id ASC)' .
            ' AND ' . self::TABLE_NAME . '.is_passed = 0',
            ['order_id' => $orderId]
        );
    }

    private function getFields(array $newFields = []): array
    {
        $table = self::TABLE_NAME;
        $fields = array_map(function (string $field) use ($table) {
            return $table . '.' . $field;
        }, self::TABLE_FIELDS);

        return array_merge($fields, $newFields);
    }
}
