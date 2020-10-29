<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\OrderLineItem\OrderLineItemFactory;
use App\DomainModel\OrderLineItem\OrderLineItemRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderLineItemRepository extends AbstractPdoRepository implements OrderLineItemRepositoryInterface
{
    public const TABLE_NAME = 'order_line_items';

    private const SELECT_FIELDS = [
        'id',
        'order_id',
        'external_id',
        'title',
        'description',
        'quantity',
        'category',
        'brand',
        'gtin',
        'mpn',
        'amount_gross',
        'amount_net',
        'amount_tax',
        'created_at',
        'updated_at',
        'order_invoice_id',
    ];

    private $factory;

    public function __construct(OrderLineItemFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(OrderLineItemEntity $orderLineItemEntity): void
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . ' 
            (
                order_id,
                external_id,
                title,
                description,
                quantity,
                category,
                brand,
                gtin,
                mpn,
                amount_gross,
                amount_net,  
                amount_tax, 
                created_at, 
                updated_at,
                order_invoice_id
            ) VALUES (
                :order_id,
                :external_id,
                :title,
                :description,
                :quantity,
                :category,
                :brand,
                :gtin,
                :mpn,
                :amount_gross,
                :amount_net,  
                :amount_tax, 
                :created_at, 
                :updated_at,
                :order_invoice_id
            )
        ', [
            'order_id' => $orderLineItemEntity->getOrderId(),
            'external_id' => $orderLineItemEntity->getExternalId(),
            'title' => $orderLineItemEntity->getTitle(),
            'description' => $orderLineItemEntity->getDescription(),
            'quantity' => $orderLineItemEntity->getQuantity(),
            'category' => $orderLineItemEntity->getCategory(),
            'brand' => $orderLineItemEntity->getBrand(),
            'gtin' => $orderLineItemEntity->getGtin(),
            'mpn' => $orderLineItemEntity->getMpn(),
            'amount_gross' => $orderLineItemEntity->getAmountGross(),
            'amount_tax' => $orderLineItemEntity->getAmountTax(),
            'amount_net' => $orderLineItemEntity->getAmountNet(),
            'created_at' => $orderLineItemEntity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $orderLineItemEntity->getUpdatedAt()->format(self::DATE_FORMAT),
            'order_invoice_id' => $orderLineItemEntity->getOrderInvoiceId(),
        ]);

        $orderLineItemEntity->setId($id);
    }

    /**
     * @return OrderLineItemEntity[]
     */
    public function getByOrderId(int $orderId): array
    {
        $rows = $this->doFetchAll(
            'SELECT ' . implode(', ', self::SELECT_FIELDS) . ' FROM ' . self::TABLE_NAME .
            ' WHERE order_id = :order_id',
            ['order_id' => $orderId]
        );

        return $rows ? $this->factory->createManyFromDatabaseRows($rows) : [];
    }
}
