<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderEntityFactory;
use App\DomainModel\Order\OrderLifecycleEvent;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateCounterDTO;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\RidProvider;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use Generator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderRepository extends AbstractPdoRepository implements OrderRepositoryInterface
{
    private const SELECT_FIELDS = 'id, uuid, amount_net, amount_gross, amount_tax, amount_forgiven, 
    duration, external_code, state, external_comment, internal_comment, invoice_number, invoice_url, 
    proof_of_delivery_url, delivery_address_id, merchant_debtor_id, merchant_id, debtor_person_id, 
    debtor_external_data_id, payment_id, created_at, updated_at, shipped_at, marked_as_fraud_at';

    private $eventDispatcher;

    private $orderFactory;

    private $ridProvider;

    public function __construct(EventDispatcherInterface $eventDispatcher, OrderEntityFactory $orderFactory, RidProvider $ridProvider)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->orderFactory = $orderFactory;
        $this->ridProvider = $ridProvider;
    }

    public function insert(OrderEntity $order): void
    {
        $id = $this->doInsert('
            INSERT INTO orders
            (
              amount_net, 
              amount_gross, 
              amount_tax, 
              amount_forgiven, 
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
              rid, 
              created_at, 
              updated_at
            ) VALUES (
              :amount_net, 
              :amount_gross, 
              :amount_tax, 
              :amount_forgiven, 
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
              :rid, 
              :created_at, 
              :updated_at
            )
        ', [
            'amount_net' => $order->getAmountNet(),
            'amount_gross' => $order->getAmountGross(),
            'amount_tax' => $order->getAmountTax(),
            'amount_forgiven' => $order->getAmountForgiven(),
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
            'uuid' => $order->getUuid(),
            'rid' => $this->ridProvider->getRid(),
            'created_at' => $order->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $order->getUpdatedAt()->format(self::DATE_FORMAT),
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

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
    }

    public function getOneByMerchantIdAndExternalCodeOrUUID(string $id, int $merchantId): ? OrderEntity
    {
        $order = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM orders
          WHERE merchant_id = :merchant_id AND (external_code = :external_code OR uuid = :uuid)
        ', [
            'merchant_id' => $merchantId,
            'external_code' => $id,
            'uuid' => $id,
        ]);

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
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

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
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

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
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

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
    }

    public function update(OrderEntity $order): void
    {
        $this->doUpdate('
            UPDATE orders
            SET
              external_code = :external_code,
              state = :state,
              merchant_debtor_id = :merchant_debtor_id,
              amount_gross = :amount_gross,
              amount_net = :amount_net,
              amount_tax = :amount_tax,
              amount_forgiven = :amount_forgiven,
              duration = :duration,
              shipped_at = :shipped_at,
              payment_id = :payment_id,
              invoice_number = :invoice_number,
              invoice_url = :invoice_url,
              proof_of_delivery_url = :proof_of_delivery_url,
              marked_as_fraud_at = :marked_as_fraud_at
            WHERE id = :id
        ', [
            'external_code' => $order->getExternalCode(),
            'amount_gross' => $order->getAmountGross(),
            'amount_net' => $order->getAmountNet(),
            'amount_tax' => $order->getAmountTax(),
            'amount_forgiven' => $order->getAmountForgiven(),
            'duration' => $order->getDuration(),
            'state' => $order->getState(),
            'merchant_debtor_id' => $order->getMerchantDebtorId(),
            'shipped_at' => $order->getShippedAt() ? $order->getShippedAt()->format(self::DATE_FORMAT) : null,
            'payment_id' => $order->getPaymentId(),
            'invoice_number' => $order->getInvoiceNumber(),
            'invoice_url' => $order->getInvoiceUrl(),
            'proof_of_delivery_url' => $order->getProofOfDeliveryUrl(),
            'marked_as_fraud_at' => $order->getMarkedAsFraudAt() ? $order->getMarkedAsFraudAt()->format(self::DATE_FORMAT)
                : null,
            'id' => $order->getId(),
        ]);

        $this->eventDispatcher->dispatch(OrderLifecycleEvent::UPDATED, new OrderLifecycleEvent($order));
    }

    public function getDebtorMaximumOverdue(int $debtorId): int
    {
        $result = $this->doFetchOne(
            '
            SELECT MAX(CEIL((:current_time - UNIX_TIMESTAMP(`shipped_at`) - (`duration` * 86400)) / 86400)) as `max_overdue`
            FROM `merchants_debtors`
            INNER JOIN `orders` ON orders.`merchant_debtor_id` = `merchants_debtors`.id
            WHERE `merchants_debtors`.debtor_id = :debtor_id
            AND `state` = :state
            AND `shipped_at` IS NOT NULL
        ',
            [
                'current_time' => time(),
                'debtor_id' => $debtorId,
                'state' => OrderStateManager::STATE_LATE,
            ]
        );

        return $result['max_overdue'] ?: 0;
    }

    public function getWithInvoiceNumber(int $limit, int $lastId = 0): Generator
    {
        $stmt = $this->doExecute(
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

        while ($row = $stmt->fetch(PdoConnection::FETCH_ASSOC)) {
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

    public function merchantDebtorHasAtLeastOneApprovedOrder(int $merchantDebtorId): bool
    {
        $result = $this->doFetchOne(
            'SELECT COUNT(id) as total
              FROM orders
              WHERE state NOT IN (:state_new, :state_declined)
              AND orders.merchant_debtor_id = :merchant_debtor_id',
            [
                'state_new' => OrderStateManager::STATE_NEW,
                'state_declined' => OrderStateManager::STATE_DECLINED,
                'merchant_debtor_id' => $merchantDebtorId,
            ]
        );

        if (!$result) {
            return false;
        }

        return $result['total'] > 0;
    }

    public function countOrdersByState(int $merchantDebtorId): OrderStateCounterDTO
    {
        $counters = [
            'total_new' => 0,
            'total_declined' => 0,
            'total_created' => 0,
            'total_canceled' => 0,
            'total_shipped' => 0,
            'total_late' => 0,
            'total_paid_out' => 0,
            'total_complete' => 0,
            'total' => 0,
            'total_active' => 0,
            'total_inactive' => 0,
            'total_new_or_declined' => 0,
        ];

        $active_states = ['created', 'shipped', 'late', 'paid_out'];
        $inactive_states = ['canceled', 'complete'];
        $failed_states = ['new', 'declined'];

        $sql = <<<SQL
    SELECT state FROM orders
    WHERE merchant_debtor_id = {$merchantDebtorId} AND marked_as_fraud_at IS NULL
SQL;
        $stmt = $this->doExecute($sql);
        while ($stmt && $row = $stmt->fetch(PdoConnection::FETCH_ASSOC)) {
            $counters['total_' . $row['state']]++;
            $counters['total']++;

            if (in_array($row['state'], $active_states)) {
                $counters['total_active']++;
            } elseif (in_array($row['state'], $inactive_states)) {
                $counters['total_inactive']++;
            } elseif (in_array($row['state'], $failed_states)) {
                $counters['total_new_or_declined']++;
            } else {
                throw new \RuntimeException("Unknown order state: {$row['state']}");
            }
        }

        return (new OrderStateCounterDTO())
            ->setTotal($counters['total'])
            ->setTotalActive($counters['total_active'])
            ->setTotalInactive($counters['total_inactive'])
            ->setTotalNew($counters['total_new'])
            ->setTotalDeclined($counters['total_declined'])
            ->setTotalCreated($counters['total_created'])
            ->setTotalCanceled($counters['total_canceled'])
            ->setTotalShipped($counters['total_shipped'])
            ->setTotalLate($counters['total_late'])
            ->setTotalPaidOut($counters['total_paid_out'])
            ->setTotalComplete($counters['total_complete']);
    }

    public function getOrdersByInvoiceHandlingStrategy(string $strategy): Generator
    {
        $stmt = $this->doExecute('
            SELECT ' . self::SELECT_FIELDS . '
            FROM orders
            WHERE invoice_url IS NOT NULL
            AND merchant_id IN (
                SELECT merchant_id FROM merchant_settings
                WHERE invoice_handling_strategy = :strategy
            );
        ', [
            'strategy' => $strategy,
        ]);

        while ($row = $stmt->fetch(PdoConnection::FETCH_ASSOC)) {
            yield $this->orderFactory->createFromDatabaseRow($row);
        }
    }

    public function getOrdersCountByMerchantDebtorAndState(int $merchantDebtorId, string $state): int
    {
        $result = $this->doFetchOne(
            'SELECT COUNT(*) as total FROM orders WHERE state = :state AND merchant_debtor_id = :merchant_debtor_id',
            [
                'state' => $state,
                'merchant_debtor_id' => $merchantDebtorId,
            ]
        );

        if (!$result) {
            return false;
        }

        return intval($result['total']);
    }
}
