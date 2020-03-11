<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
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
    ];

    private $factory;

    public function __construct(OrderFinancialDetailsFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(OrderFinancialDetailsEntity $orderFinancialDetailsEntity): void
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . ' 
            (
                order_id,
                amount_gross,
                amount_net,  
                amount_tax, 
                duration, 
                created_at, 
                updated_at
            ) VALUES (
                :order_id,
                :amount_gross,
                :amount_net,  
                :amount_tax, 
                :duration, 
                :created_at, 
                :updated_at
            )
        ', [
            'order_id' => $orderFinancialDetailsEntity->getOrderId(),
            'amount_gross' => $orderFinancialDetailsEntity->getAmountGross()->getMoneyValue(),
            'amount_net' => $orderFinancialDetailsEntity->getAmountNet()->getMoneyValue(),
            'amount_tax' => $orderFinancialDetailsEntity->getAmountTax()->getMoneyValue(),
            'duration' => $orderFinancialDetailsEntity->getDuration(),
            'created_at' => $orderFinancialDetailsEntity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $orderFinancialDetailsEntity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $orderFinancialDetailsEntity->setId($id);
    }

    public function getCurrentByOrderId(int $orderId): ? OrderFinancialDetailsEntity
    {
        $row = $this->doFetchOne(
            'SELECT ' . implode(', ', self::SELECT_FIELDS) . ' FROM ' . self::TABLE_NAME . ' 
            WHERE order_id = :order_id ORDER BY id DESC LIMIT 1',
            ['order_id' => $orderId]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
