<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderEntityFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateCounterDTO;
use Billie\MonitoringBundle\Service\RidProvider;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityRepositoryInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityRepositoryTrait;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use Generator;

class OrderRepository extends AbstractPdoRepository implements
    OrderRepositoryInterface,
    StatefulEntityRepositoryInterface
{
    use StatefulEntityRepositoryTrait;

    public const TABLE_NAME = 'orders';

    public const SELECT_FIELDS = [
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
        'workflow_name',
        'created_at',
        'updated_at',
        'shipped_at',
        'checkout_session_id',
        'company_billing_address_uuid',
        'creation_source',
        'duration_extension',
    ];

    private OrderEntityFactory $orderFactory;

    private RidProvider $ridProvider;

    public function __construct(OrderEntityFactory $orderFactory, RidProvider $ridProvider)
    {
        $this->orderFactory = $orderFactory;
        $this->ridProvider = $ridProvider;
    }

    public function insert(OrderEntity $order): void
    {
        $id = $this->doInsert(
            '
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
              workflow_name,
              uuid, 
              rid, 
              company_billing_address_uuid,
              creation_source,
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
              :workflow_name,
              :uuid, 
              :rid,
              :company_billing_address_uuid,
              :creation_source,
              :created_at, 
              :updated_at
            )
        ',
            [
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
                'workflow_name' => $order->getWorkflowName(),
                'created_at' => $order->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $order->getUpdatedAt()->format(self::DATE_FORMAT),
                'merchant_debtor_id' => $order->getMerchantDebtorId(),
                'company_billing_address_uuid' => $order->getCompanyBillingAddressUuid(),
                'creation_source' => $order->getCreationSource(),
            ]
        );

        $order->setId($id);
        $this->dispatchCreatedEvent($order);
    }

    public function getOneByExternalCodeAndMerchantId(string $externalCode, int $merchantId): ?OrderEntity
    {
        $order = $this->doFetchOne(
            '
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE external_code = :external_code AND merchant_id = :merchant_id
        ',
            [
                'external_code' => $externalCode,
                'merchant_id' => $merchantId,
            ]
        );

        return $order ? $this->orderFactory->createFromArray($order) : null;
    }

    public function getOneByMerchantIdAndExternalCodeOrUUID(string $id, int $merchantId): ?OrderEntity
    {
        $order = $this->doFetchOne(
            '
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE merchant_id = :merchant_id AND (external_code = :external_code OR uuid = :uuid)
        ',
            [
                'merchant_id' => $merchantId,
                'external_code' => $id,
                'uuid' => $id,
            ]
        );

        return $order ? $this->orderFactory->createFromArray($order) : null;
    }

    public function getOneByMerchantIdAndUUID(string $uuid, int $merchantId): ?OrderEntity
    {
        $order = $this->doFetchOne(
            '
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE merchant_id = :merchant_id AND uuid = :uuid
        ',
            [
                'merchant_id' => $merchantId,
                'uuid' => $uuid,
            ]
        );

        return $order ? $this->orderFactory->createFromArray($order) : null;
    }

    public function getOneByPaymentId(string $paymentId): ?OrderEntity
    {
        $order = $this->doFetchOne(
            '
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM orders
          WHERE payment_id = :payment_id
        ',
            [
                'payment_id' => $paymentId,
            ]
        );

        return $order ? $this->orderFactory->createFromArray($order) : null;
    }

    public function getOneById(int $id): ?OrderEntity
    {
        $order = $this->doFetchOne(
            '
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . ' 
          WHERE id = :id
        ',
            [
                'id' => $id,
            ]
        );

        return $order ? $this->orderFactory->createFromArray($order) : null;
    }

    public function getOneByUuid(string $uuid): ?OrderEntity
    {
        $order = $this->doFetchOne(
            '
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE uuid = :uuid
        ',
            [
                'uuid' => $uuid,
            ]
        );

        return $order ? $this->orderFactory->createFromArray($order) : null;
    }

    public function getByInvoice(string $invoiceUuid): array
    {
        $orders = $this->doFetchAll(
            'SELECT ' . implode(', ', array_map(fn ($f) => 'o.' . $f, self::SELECT_FIELDS)) .
            ' FROM ' . self::TABLE_NAME . ' o ' .
            ' LEFT JOIN order_invoices_v2 inv ON inv.order_id = o.id ' .
            ' WHERE inv.invoice_uuid = :uuid',
            ['uuid' => $invoiceUuid]
        );

        return $this->orderFactory->createFromArrayMultiple($orders);
    }

    public function getByInvoiceAndMerchant(string $invoiceUuid, int $merchantId): ?OrderEntity
    {
        $order = $this->doFetchOne(
            'SELECT ' . implode(', ', array_map(fn ($f) => 'o.' . $f, self::SELECT_FIELDS)) .
            ' FROM ' . self::TABLE_NAME . ' o ' .
            ' LEFT JOIN order_invoices_v2 inv ON inv.order_id = o.id ' .
            ' WHERE inv.invoice_uuid = :uuid AND o.merchant_id = :merchant_id',
            ['uuid' => $invoiceUuid, 'merchant_id' => $merchantId]
        );

        return $order ? $this->orderFactory->createFromArray($order) : null;
    }

    public function updateDurationExtension(int $orderId, int $durationExtension): void
    {
        $this->doUpdate(
            'UPDATE ' . self::TABLE_NAME .
            ' SET duration_extension = :duration_extension ' .
            ' WHERE id = :id ',
            [
                'duration_extension' => $durationExtension,
                'id' => $orderId,
            ]
        );
    }

    public function updateOrderExternalCode(OrderEntity $orderEntity): void
    {
        $this->doUpdate(
            'UPDATE ' . self::TABLE_NAME .
            ' SET external_code = :external_code ' .
            ' WHERE id = :id ',
            [
                'external_code' => $orderEntity->getExternalCode(),
                'id' => $orderEntity->getId(),
            ]
        );
    }

    public function update(OrderEntity $order): void
    {
        $this->doUpdate(
            '
            UPDATE ' . self::TABLE_NAME . '
            SET
              external_code = :external_code,
              state = :state,
              merchant_debtor_id = :merchant_debtor_id,
              amount_forgiven = :amount_forgiven,
              shipped_at = :shipped_at,
              updated_at = :updated_at,
              payment_id = :payment_id,
              invoice_number = :invoice_number,
              invoice_url = :invoice_url,
              proof_of_delivery_url = :proof_of_delivery_url,
              company_billing_address_uuid = :company_billing_address_uuid,
              creation_source = :creation_source
            WHERE id = :id
        ',
            [
                'external_code' => $order->getExternalCode(),
                'state' => $order->getState(),
                'merchant_debtor_id' => $order->getMerchantDebtorId(),
                'amount_forgiven' => $order->getAmountForgiven(),
                'shipped_at' => $order->getShippedAt() ? $order->getShippedAt()->format(self::DATE_FORMAT) : null,
                'updated_at' => (new \DateTime())->format(self::DATE_FORMAT),
                'payment_id' => $order->getPaymentId(),
                'invoice_number' => $order->getInvoiceNumber(),
                'invoice_url' => $order->getInvoiceUrl(),
                'proof_of_delivery_url' => $order->getProofOfDeliveryUrl(),
                'company_billing_address_uuid' => $order->getCompanyBillingAddressUuid(),
                'creation_source' => $order->getCreationSource(),
                'id' => $order->getId(),
            ]
        );
    }

    public function updateMerchantDebtor(int $orderId, int $merchantDebtorId): void
    {
        $this->doUpdate(
            '
            UPDATE ' . self::TABLE_NAME . '
            SET
              merchant_debtor_id = :merchant_debtor_id,
              updated_at = :updated_at
            WHERE id = :id
        ',
            [
                'merchant_debtor_id' => $merchantDebtorId,
                'updated_at' => (new \DateTime())->format(self::DATE_FORMAT),
                'id' => $orderId,
            ]
        );
    }

    public function updateIdentificationBillingAddress(int $orderId, string $billingAddressUuid): void
    {
        $this->doUpdate(
            '
            UPDATE ' . self::TABLE_NAME . '
            SET
              company_billing_address_uuid = :company_billing_address_uuid,
              updated_at = :updated_at
            WHERE id = :id
        ',
            [
                'company_billing_address_uuid' => $billingAddressUuid,
                'updated_at' => (new \DateTime())->format(self::DATE_FORMAT),
                'id' => $orderId,
            ]
        );
    }

    public function getDebtorMaximumOverdue(string $companyUuid): int
    {
        $result = $this->doFetchOne(
            '
            SELECT MAX(CEIL((:current_time - UNIX_TIMESTAMP(shipped_at) - (order_financial_details.duration * 86400)) / 86400)) as max_overdue
            FROM merchants_debtors
            INNER JOIN orders ON orders.merchant_debtor_id = merchants_debtors.id
            INNER JOIN order_financial_details ON order_financial_details.order_id = orders.id
            WHERE merchants_debtors.company_uuid = :company_uuid
            AND state = :state
            AND shipped_at IS NOT NULL
        ',
            [
                'current_time' => time(),
                'company_uuid' => $companyUuid,
                'state' => OrderEntity::STATE_LATE,
            ]
        );

        return $result['max_overdue'] ?: 0;
    }

    public function debtorHasAtLeastOneFullyPaidOrder(string $companyUuid): bool
    {
        $result = $this->doFetchOne(
            'SELECT COUNT(id) as total
              FROM ' . self::TABLE_NAME . '
              WHERE state = :state
              AND orders.merchant_debtor_id IN (SELECT id FROM merchants_debtors WHERE company_uuid = :company_uuid)',
            [
                'state' => OrderEntity::STATE_COMPLETE,
                'company_uuid' => $companyUuid,
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
    WHERE merchant_debtor_id = {$merchantDebtorId}
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
        $stmt = $this->doExecute(
            '
            SELECT ' . implode(', ', self::SELECT_FIELDS) . '
            FROM orders
            WHERE invoice_url IS NOT NULL
            AND merchant_id IN (
                SELECT merchant_id FROM merchant_settings
                WHERE invoice_handling_strategy = :strategy
            );
        ',
            [
                'strategy' => $strategy,
            ]
        );

        while ($row = $stmt->fetch(PdoConnection::FETCH_ASSOC)) {
            yield $this->orderFactory->createFromArray($row);
        }
    }

    public function getNotYetConfirmedByCheckoutSessionUuid(string $checkoutSessionUuid): ?OrderEntity
    {
        $states = array_map(
            function ($state) {
                return "'{$state}'";
            },
            [OrderEntity::STATE_PRE_WAITING, OrderEntity::STATE_AUTHORIZED]
        );

        $sql = 'SELECT orders.' . implode(', orders.', self::SELECT_FIELDS) . ' FROM orders
            INNER JOIN checkout_sessions ch ON ch.id = orders.checkout_session_id
            WHERE ch.uuid = :checkout_session_uuid AND orders.state IN (' . implode(',', $states) . ')
        ';

        $row = $this->doFetchOne(
            $sql,
            [
                'checkout_session_uuid' => $checkoutSessionUuid,
            ]
        );

        return $row ? $this->orderFactory->createFromArray($row) : null;
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

        return (int) $result['total'];
    }

    public function getOrdersCountByCompanyBillingAddressAndState(
        string $companyUuid,
        string $addressUuid,
        string $state
    ): int {
        $result = $this->doFetchOne(
            'SELECT COUNT(*) as total FROM ' . self::TABLE_NAME . ' o
            JOIN merchants_debtors md ON o.merchant_debtor_id = md.id 
            WHERE state = :state AND o.company_billing_address_uuid = :address_uuid AND md.company_uuid = :company_uuid',
            [
                'state' => $state,
                'address_uuid' => $addressUuid,
                'company_uuid' => $companyUuid,
            ]
        );

        if (!$result) {
            return 0;
        }

        return (int) $result['total'];
    }

    public function geOrdersByMerchantId(int $merchantId, \DateTime $shippedFrom, int $limit): array
    {
        $tableOrders = self::TABLE_NAME;
        $tableInvoicesV1 = LegacyOrderInvoiceRepository::TABLE_NAME;
        $allFields = 'o.' . implode(', o.', self::SELECT_FIELDS);

        $sql = <<<SQL
SELECT
	$allFields
FROM
	{$tableOrders} o
	LEFT JOIN {$tableInvoicesV1} i on o.id = i.order_id
WHERE
	o.shipped_at >= :shipped_from
	AND o.merchant_id = :merchant_id
	AND i.id IS NULL
LIMIT $limit
;
SQL;

        $rows = $this->doFetchAll(
            $sql,
            [
                'shipped_from' => $shippedFrom->format('c'),
                'merchant_id' => $merchantId,
            ]
        );

        return array_map([$this->orderFactory, 'createFromArray'], $rows);
    }
}
