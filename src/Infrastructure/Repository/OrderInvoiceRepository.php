<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderInvoice\OrderInvoiceCollection;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceFactory;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderInvoiceRepository extends AbstractPdoRepository implements OrderInvoiceRepositoryInterface
{
    public const TABLE_NAME = 'order_invoices_v2';

    private const SELECT_FIELDS = [
        'id',
        'order_id',
        'invoice_uuid',
        'created_at',
    ];

    private OrderInvoiceFactory $factory;

    public function __construct(OrderInvoiceFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(OrderInvoiceEntity $orderInvoiceEntity): OrderInvoiceEntity
    {
        $id = $this->doInsert(
            '
            INSERT INTO ' . self::TABLE_NAME . '
            (order_id, invoice_uuid, created_at)
            VALUES
            (:order_id, :invoice_uuid, :created_at)
        ',
            [
                'order_id' => $orderInvoiceEntity->getOrderId(),
                'invoice_uuid' => $orderInvoiceEntity->getInvoiceUuid(),
                'created_at' => $orderInvoiceEntity->getCreatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $orderInvoiceEntity->setId($id);

        return $orderInvoiceEntity;
    }

    public function findByOrderId(int $orderId): OrderInvoiceCollection
    {
        return $this->findByOrderIds([$orderId]);
    }

    public function findByOrderIds(array $orderIds): OrderInvoiceCollection
    {
        if (empty($orderIds)) {
            return new OrderInvoiceCollection([]);
        }

        $rows = $this->doFetchAll(
            'SELECT ' . implode(', ', self::SELECT_FIELDS) . ' FROM ' . self::TABLE_NAME .
            ' WHERE order_id IN (' . implode(',', $orderIds) . ')'
        );

        return new OrderInvoiceCollection($rows ? $this->factory->createFromArrayCollection($rows) : []);
    }

    public function getByUuidAndMerchant(string $invoiceUuid, int $merchantId): ?OrderInvoiceEntity
    {
        $invoice = $this->doFetchOne(
            'SELECT ' . implode(', ', array_map(fn ($f) => 'inv.' . $f, self::SELECT_FIELDS)) .
            ' FROM ' . self::TABLE_NAME . ' inv ' .
            ' LEFT JOIN orders o ON inv.order_id = o.id ' .
            ' WHERE inv.invoice_uuid = :uuid AND o.merchant_id = :merchant_id',
            ['uuid' => $invoiceUuid, 'merchant_id' => $merchantId]
        );

        return $invoice ? $this->factory->createFromArray($invoice) : null;
    }

    public function getByUuid(string $invoiceUuid): ?OrderInvoiceEntity
    {
        $invoice = $this->doFetchOne(
            'SELECT ' . implode(', ', self::SELECT_FIELDS) . ' FROM ' . self::TABLE_NAME .
            ' WHERE invoice_uuid = :uuid',
            ['uuid' => $invoiceUuid]
        );

        return $invoice ? $this->factory->createFromArray($invoice) : null;
    }
}
