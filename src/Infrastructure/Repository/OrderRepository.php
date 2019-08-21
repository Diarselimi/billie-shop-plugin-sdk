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
use Symfony\Component\HttpFoundation\ParameterBag;

class OrderRepository extends AbstractPdoRepository implements OrderRepositoryInterface
{
    const TABLE_NAME = 'orders';

    private const SELECT_FIELDS = [
        'id',
        'uuid',
        'amount_forgiven',
        'external_code',
        'state',
        'external_comment',
        'internal_comment',
        'invoice_number',
        'invoice_url',
        'proof_of_delivery_url',
        'delivery_address_id',
        'merchant_debtor_id',
        'merchant_id',
        'debtor_person_id',
        'debtor_external_data_id',
        'payment_id',
        'created_at',
        'updated_at',
        'shipped_at',
        'marked_as_fraud_at',
        'checkout_session_id',
    ];

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
            INSERT INTO ' . self::TABLE_NAME . '
            (
              amount_forgiven, 
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
              checkout_session_id, 
              uuid, 
              rid, 
              created_at, 
              updated_at
            ) VALUES (
              :amount_forgiven, 
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
              :checkout_session_id,
              :uuid, 
              :rid,
              :created_at, 
              :updated_at
            )
        ', [
            'amount_forgiven' => $order->getAmountForgiven(),
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
            'checkout_session_id' => $order->getCheckoutSessionId(),
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
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE external_code = :external_code AND merchant_id = :merchant_id
        ', [
            'external_code' => $externalCode,
            'merchant_id' => $merchantId,
        ]);

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
    }

    public function getOneByMerchantIdAndExternalCodeOrUUID(string $id, int $merchantId): ?OrderEntity
    {
        $order = $this->doFetchOne('
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
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
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
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
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . ' 
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
    }

    public function getOneByUuid(string $uuid): ?OrderEntity
    {
        $order = $this->doFetchOne('
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE uuid = :uuid
        ', [
            'uuid' => $uuid,
        ]);

        return $order ? $this->orderFactory->createFromDatabaseRow($order) : null;
    }

    public function update(OrderEntity $order): void
    {
        $this->doUpdate('
            UPDATE ' . self::TABLE_NAME . '
            SET
              external_code = :external_code,
              state = :state,
              merchant_debtor_id = :merchant_debtor_id,
              amount_forgiven = :amount_forgiven,
              shipped_at = :shipped_at,
              payment_id = :payment_id,
              invoice_number = :invoice_number,
              invoice_url = :invoice_url,
              proof_of_delivery_url = :proof_of_delivery_url,
              marked_as_fraud_at = :marked_as_fraud_at
            WHERE id = :id
        ', [
            'external_code' => $order->getExternalCode(),
            'amount_forgiven' => $order->getAmountForgiven(),
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
            SELECT MAX(CEIL((:current_time - UNIX_TIMESTAMP(shipped_at) - (order_financial_details.duration * 86400)) / 86400)) as max_overdue
            FROM merchants_debtors
            INNER JOIN orders ON orders.merchant_debtor_id = merchants_debtors.id
            INNER JOIN order_financial_details ON order_financial_details.order_id = orders.id
            WHERE merchants_debtors.debtor_id = :debtor_id
            AND state = :state
            AND shipped_at IS NOT NULL
        ',
            [
                'current_time' => time(),
                'debtor_id' => $debtorId,
                'state' => OrderStateManager::STATE_LATE,
            ]
        );

        return $result['max_overdue'] ?: 0;
    }

    public function debtorHasAtLeastOneFullyPaidOrder(int $debtorId): bool
    {
        $result = $this->doFetchOne(
            'SELECT COUNT(id) as total
              FROM ' . self::TABLE_NAME . '
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
              FROM ' . self::TABLE_NAME . '
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
            SELECT ' . implode(', ', self::SELECT_FIELDS) . '
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

    public function getAuthorizedByCheckoutSessionUuid(string $checkoutSessionUuid): ?OrderEntity
    {
        $sql = 'SELECT orders.'  . implode(', orders.', self::SELECT_FIELDS) . ' FROM orders
            INNER JOIN checkout_sessions ch ON ch.id = orders.checkout_session_id
            WHERE ch.uuid = :checkout_session_uuid AND orders.state = :authorized_state
        ';

        $row = $this->doFetchOne(
            $sql,
            [
                'checkout_session_uuid' => $checkoutSessionUuid,
                'authorized_state' => OrderStateManager::STATE_AUTHORIZED,
            ]
        );

        return $row ? $this->orderFactory->createFromDatabaseRow($row) : null;
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

    public function search(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection,
        ?string $searchString,
        array $filters
    ): array {
        $query = 'SELECT %s FROM orders';
        $filters = new ParameterBag($filters);

        if ($filters->has('merchant_debtor_id')) {
            $query .= ' INNER JOIN merchants_debtors ON orders.merchant_debtor_id = merchants_debtors.id AND merchants_debtors.uuid = :merchant_debtor_id';
            $queryParameters['merchant_debtor_id'] = $filters->get('merchant_debtor_id');
        }

        if ($sortBy === 'amount_gross') {
            $query .= ' INNER JOIN order_financial_details ON order_financial_details.order_id = orders.id';
            $query .= ' INNER JOIN (SELECT MAX(id) AS maxID FROM order_financial_details GROUP BY order_id) AS of ON of.maxID = order_financial_details.id';
        }

        $query .= ' WHERE orders.merchant_id = :merchant_id AND state != :state_new';
        $queryParameters['merchant_id'] = $merchantId;
        $queryParameters['state_new'] = OrderStateManager::STATE_NEW;

        if ($searchString) {
            $query .= ' AND (orders.external_code LIKE :search OR orders.uuid LIKE :search OR orders.invoice_number LIKE :search )';

            $queryParameters['search'] = '%' . $searchString . '%';
        }

        if ($filters->has('state') && is_array($filters->get('state')) && !empty($filters->get('state'))) {
            $states = array_map(function ($state) {
                return "'{$state}'";
            }, $filters->get('state'));

            $query .= ' AND orders.state IN (' . implode(',', $states) . ')';
        }

        $totalCount = $this->doFetchOne(sprintf($query, 'COUNT(*) as total_count'), $queryParameters);

        $query .= " ORDER BY $sortBy $sortDirection LIMIT $offset,$limit";

        $rows = $this->doFetchAll(
            sprintf($query, 'orders.' . implode(', orders.', self::SELECT_FIELDS)),
            $queryParameters
        );

        return [
            'total' => $totalCount['total_count'] ?? 0,
            'orders' => array_map([$this->orderFactory, 'createFromDatabaseRow'], $rows),
        ];
    }
}
