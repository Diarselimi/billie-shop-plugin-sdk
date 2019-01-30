<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderEntityFactory;
use App\DomainModel\Order\OrderLifecycleEvent;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\Infrastructure\PDO\PDO;
use Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    const SELECT_FIELDS = 'id, amount_net, amount_gross, amount_tax, duration, external_code, state, external_comment, internal_comment, invoice_number, invoice_url, proof_of_delivery_url, delivery_address_id, merchant_debtor_id, merchant_id, debtor_person_id, debtor_external_data_id, payment_id, created_at, updated_at, shipped_at, marked_as_fraud_at';

    private $eventDispatcher;

    private $orderFactory;

    public function __construct(EventDispatcherInterface $eventDispatcher, OrderEntityFactory $orderFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->orderFactory = $orderFactory;
    }

    public function insert(OrderEntity $order): void
    {
        $id = $this->doInsert('
            INSERT INTO orders
            (
              amount_net, 
              amount_gross, 
              amount_tax, 
              duration, 
              external_code, 
              state, 
              external_comment, 
              internal_comment, 
              invoice_number, 
              invoice_url, 
              proof_of_delivery_url, 
              delivery_address_id, 
              merchant_id, 
              debtor_person_id, 
              debtor_external_data_id, 
              merchant_debtor_id,
              payment_id, 
              uuid, 
              created_at, 
              updated_at
            ) VALUES (
              :amount_net, 
              :amount_gross, 
              :amount_tax, 
              :duration, 
              :external_code, 
              :state, 
              :external_comment, 
              :internal_comment, 
              :invoice_number, 
              :invoice_url, 
              :proof_of_delivery_url, 
              :delivery_address_id, 
              :merchant_id, 
              :debtor_person_id, 
              :debtor_external_data_id, 
              :merchant_debtor_id,
              :payment_id, 
              :uuid, 
              :created_at, 
              :updated_at
            )
        ', [
            'amount_net' => $order->getAmountNet(),
            'amount_gross' => $order->getAmountGross(),
            'amount_tax' => $order->getAmountTax(),
            'duration' => $order->getDuration(),
            'external_code' => $order->getExternalCode(),
            'state' => $order->getState(),
            'external_comment' => $order->getExternalComment(),
            'internal_comment' => $order->getInternalComment(),
            'invoice_number' => $order->getInvoiceNumber(),
            'invoice_url' => $order->getInvoiceUrl(),
            'proof_of_delivery_url' => $order->getProofOfDeliveryUrl(),
            'delivery_address_id' => $order->getDeliveryAddressId(),
            'merchant_id' => $order->getMerchantId(),
            'debtor_person_id' => $order->getDebtorPersonId(),
            'debtor_external_data_id' => $order->getDebtorExternalDataId(),
            'payment_id' => $order->getPaymentId(),
            'uuid' => Uuid::uuid4()->toString(),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
            'merchant_debtor_id' => $order->getMerchantDebtorId(),
        ]);

        $order->setId($id);
        $this->eventDispatcher->dispatch(OrderLifecycleEvent::CREATED, new OrderLifecycleEvent($order));
    }

    public function getOneByExternalCode(string $externalCode, int $merchantId): ?OrderEntity
    {
        $order = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM orders
          WHERE external_code = :external_code AND merchant_id = :merchant_id
        ', [
            'external_code' => $externalCode,
            'merchant_id' => $merchantId,
        ]);

        if (!$order) {
            return null;
        }

        return $this->orderFactory->createFromDatabaseRow($order);
    }

    public function getOneByPaymentId(string $paymentId): ?OrderEntity
    {
        $order = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM orders
          WHERE payment_id = :payment_id
        ', [
            'payment_id' => $paymentId,
        ]);

        if (!$order) {
            return null;
        }

        return $this->orderFactory->createFromDatabaseRow($order);
    }

    public function getOneById(int $id): ?OrderEntity
    {
        $order = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM orders
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        if (!$order) {
            return null;
        }

        return $this->orderFactory->createFromDatabaseRow($order);
    }

    public function getOneByUuid(string $uuid): ?OrderEntity
    {
        $order = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM orders
          WHERE uuid = :uuid
        ', [
            'uuid' => $uuid,
        ]);

        if (!$order) {
            return null;
        }

        return $this->orderFactory->createFromDatabaseRow($order);
    }

    public function update(OrderEntity $order): void
    {
        $this->doUpdate('
            UPDATE orders
            SET
              state = :state,
              merchant_debtor_id = :merchant_debtor_id,
              amount_gross = :amount_gross,
              amount_net = :amount_net,
              amount_tax = :amount_tax,
              duration = :duration,
              shipped_at = :shipped_at,
              payment_id = :payment_id,
              invoice_number = :invoice_number,
              invoice_url = :invoice_url,
              proof_of_delivery_url = :proof_of_delivery_url,
              marked_as_fraud_at = :marked_as_fraud_at
            WHERE id = :id
        ', [
            'amount_gross' => $order->getAmountGross(),
            'amount_net' => $order->getAmountNet(),
            'amount_tax' => $order->getAmountTax(),
            'duration' => $order->getDuration(),
            'state' => $order->getState(),
            'merchant_debtor_id' => $order->getMerchantDebtorId(),
            'shipped_at' => $order->getShippedAt() ? $order->getShippedAt()->format('Y-m-d H:i:s') : null,
            'payment_id' => $order->getPaymentId(),
            'invoice_number' => $order->getInvoiceNumber(),
            'invoice_url' => $order->getInvoiceUrl(),
            'proof_of_delivery_url' => $order->getProofOfDeliveryUrl(),
            'marked_as_fraud_at' => $order->getMarkedAsFraudAt() ? $order->getMarkedAsFraudAt()->format('Y-m-d H:i:s')
                : null,
            'id' => $order->getId(),
        ]);

        $this->eventDispatcher->dispatch(OrderLifecycleEvent::UPDATED, new OrderLifecycleEvent($order));
    }

    /**
     * @param int $merchantDebtorId
     *
     * @return Generator|int[]
     */
    public function getCustomerOverdues(int $merchantDebtorId): Generator
    {
        $query = '
            SELECT CEIL((:current_time - UNIX_TIMESTAMP(`shipped_at`) - (`duration` * 86400)) / 86400) as `overdue`
            FROM `orders`
            WHERE `merchant_debtor_id` = :merchant_debtor_id
            AND `state` = :state
            AND `shipped_at` IS NOT NULL
        ';

        $params = [
            'current_time' => time(),
            'merchant_debtor_id' => $merchantDebtorId,
            'state' => OrderStateManager::STATE_LATE,
        ];

        $stmt = $this->exec($query, $params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['overdue'] > 0) {
                yield (int) $row['overdue'];
            }
        }
    }

    public function getWithInvoiceNumber(int $limit, int $lastId = 0): Generator
    {
        $stmt = $this->exec(
            'SELECT id, external_code, merchant_id, invoice_number
              FROM orders
              WHERE invoice_number IS NOT NULL
              AND id > :lastId
              AND NOT EXISTS (SELECT * FROM order_invoices WHERE order_invoices.order_id = orders.id)
              ORDER BY id ASC
              LIMIT ' . $limit,
            [
                'lastId' => $lastId,
            ]
        );

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function debtorHasAtLeastOneFullyPaidOrder(int $debtorId): bool
    {
        $result = $this->doFetchOne(
            'SELECT COUNT(id) as total
              FROM orders
              WHERE state = :state
              AND orders.merchant_debtor_id IN (SELECT id FROM merchants_debtors WHERE debtor_id = :debtor_id)',
            [
                'state' => OrderStateManager::STATE_COMPLETE,
                'debtor_id' => $debtorId,
            ]
        );

        if (!$result) {
            return false;
        }

        return $result['total'] > 0;
    }
}
