<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsCollection;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderFinancialDetailsRepository extends AbstractPdoRepository implements OrderFinancialDetailsRepositoryInterface
{
    public const TABLE_NAME = 'order_financial_details';

    private const SELECT_FIELDS = [
        'id',
        'order_id',
        'amount_gross',
        'amount_net',
        'amount_tax',
        'duration',
        'created_at',
        'updated_at',
        'unshipped_amount_gross',
        'unshipped_amount_net',
        'unshipped_amount_tax',
    ];

    private $factory;

    public function __construct(OrderFinancialDetailsFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(OrderFinancialDetailsEntity $orderFinancialDetailsEntity): void
    {
        $id = $this->doInsert(
            '
            INSERT INTO ' . self::TABLE_NAME . ' 
            (
                order_id,
                amount_gross,
                amount_net,  
                amount_tax, 
                duration, 
                created_at, 
                updated_at,
                unshipped_amount_gross,
                unshipped_amount_net,
                unshipped_amount_tax
            ) VALUES (
                :order_id,
                :amount_gross,
                :amount_net,  
                :amount_tax, 
                :duration, 
                :created_at, 
                :updated_at,
                :unshipped_amount_gross,
                :unshipped_amount_net,
                :unshipped_amount_tax
            )
        ',
            [
                'order_id' => $orderFinancialDetailsEntity->getOrderId(),
                'amount_gross' => $orderFinancialDetailsEntity->getAmountGross()->getMoneyValue(),
                'amount_net' => $orderFinancialDetailsEntity->getAmountNet()->getMoneyValue(),
                'amount_tax' => $orderFinancialDetailsEntity->getAmountTax()->getMoneyValue(),
                'unshipped_amount_gross' => $orderFinancialDetailsEntity->getUnshippedAmountGross()->getMoneyValue(),
                'unshipped_amount_net' => $orderFinancialDetailsEntity->getUnshippedAmountNet()->getMoneyValue(),
                'unshipped_amount_tax' => $orderFinancialDetailsEntity->getUnshippedAmountTax()->getMoneyValue(),
                'duration' => $orderFinancialDetailsEntity->getDuration(),
                'created_at' => $orderFinancialDetailsEntity->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $orderFinancialDetailsEntity->getUpdatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $orderFinancialDetailsEntity->setId($id);
    }

    public function getLatestByOrderId(int $orderId): ?OrderFinancialDetailsEntity
    {
        $row = $this->doFetchOne(
            'SELECT ' . implode(', ', self::SELECT_FIELDS) . ' FROM ' . self::TABLE_NAME . ' 
            WHERE order_id = :order_id ORDER BY id DESC LIMIT 1',
            ['order_id' => $orderId]
        );

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getLatestByOrderIds(array $orderIds): OrderFinancialDetailsCollection
    {
        if (empty($orderIds)) {
            return new OrderFinancialDetailsCollection([]);
        }

        $sql = $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS)
            . ' WHERE id IN (' . $this->generateSelectQuery(self::TABLE_NAME, ['MAX(id)'])
            . '   WHERE order_id IN (' . implode(',', $orderIds) . ') GROUP BY order_id)';

        return $this->factory->createCollection($this->doFetchAll($sql));
    }

    public function getLatestByOrderUuid(string $orderUuid): ?OrderFinancialDetailsEntity
    {
        $selectFields = array_map(static fn ($field) => self::TABLE_NAME . '.' . $field, self::SELECT_FIELDS);
        $sql = $this->generateSelectQuery(self::TABLE_NAME, $selectFields);
        $sql .= ' INNER JOIN orders o ON o.id = ' . self::TABLE_NAME . '.order_id';
        $sql .= ' WHERE o.uuid = :order_uuid ORDER BY ' . self::TABLE_NAME . '.id DESC LIMIT 1';

        $row = $this->doFetchOne($sql, ['order_uuid' => $orderUuid]);

        return $row ? $this->factory->createFromArray($row) : null;
    }
}
