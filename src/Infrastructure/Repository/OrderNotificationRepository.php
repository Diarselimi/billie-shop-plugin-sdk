<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderNotification\OrderNotificationDeliveryRepositoryInterface;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationFactory;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;
use DateTime;

class OrderNotificationRepository extends AbstractPdoRepository implements OrderNotificationRepositoryInterface
{
    private const SELECT_FIELDS = 'id, order_id, invoice_uuid, notification_type, payload, is_delivered, created_at, updated_at';

    private $factory;

    private $deliveryRepository;

    public function __construct(
        OrderNotificationFactory $factory,
        OrderNotificationDeliveryRepositoryInterface $deliveryRepository
    ) {
        $this->factory = $factory;
        $this->deliveryRepository = $deliveryRepository;
    }

    public function insert(OrderNotificationEntity $orderNotification): void
    {
        $id = $this->doInsert('
            INSERT INTO order_notifications
            (order_id, invoice_uuid, notification_type, payload, is_delivered, created_at, updated_at)
            VALUES
            (:order_id, :invoice_uuid, :notification_type, :payload, :is_delivered, :created_at, :updated_at)
        ', [
            'order_id' => $orderNotification->getOrderId(),
            'invoice_uuid' => $orderNotification->getInvoiceUuid(),
            'notification_type' => $orderNotification->getNotificationType(),
            'payload' => json_encode($orderNotification->getPayload()),
            'is_delivered' => (int) $orderNotification->isDelivered(),
            'created_at' => $orderNotification->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $orderNotification->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $orderNotification->setId($id);
    }

    public function update(OrderNotificationEntity $orderNotification): void
    {
        $orderNotification->setUpdatedAt(new DateTime());
        $this->doUpdate('
            UPDATE order_notifications
            SET 
              is_delivered = :is_delivered,
              updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $orderNotification->getId(),
            'is_delivered' => (int) $orderNotification->isDelivered(),
            'updated_at' => $orderNotification->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        foreach ($orderNotification->getDeliveries() as $delivery) {
            if (!$delivery->getId()) {
                $this->deliveryRepository->insert($delivery);
            }
        }
    }

    public function getOneById(int $id): ? OrderNotificationEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM order_notifications
          WHERE id = :id
        ', ['id' => $id]);

        if (!$row) {
            return null;
        }

        $deliveries = $this->deliveryRepository->getAllByNotificationId($id);

        return $this->factory
            ->createFromDatabaseRow($row)
            ->setDeliveries($deliveries)
        ;
    }

    /**
     * @return OrderNotificationEntity[]
     */
    public function getFailedByOrderId(int $orderId): array
    {
        $notifications = $this->doFetchAll(
            'SELECT ' . self::SELECT_FIELDS . ' FROM order_notifications 
            WHERE order_id = :order_id AND is_delivered = 0 
            ORDER BY created_at ASC',
            ['order_id' => $orderId]
        );

        return $this->factory->createMultipleFromDatabaseRows($notifications);
    }

    public function getOneByOrderIdAndNotificationType(int $orderId, string $notificationType): ?OrderNotificationEntity
    {
        $row = $this->doFetchOne(
            'SELECT ' . self::SELECT_FIELDS . '
            FROM order_notifications WHERE order_id = :order_id AND notification_type = :notification_type
          ',
            [
                'order_id' => $orderId,
                'notification_type' => $notificationType,
            ]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
