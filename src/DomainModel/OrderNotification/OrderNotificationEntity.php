<?php

namespace App\DomainModel\OrderNotification;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class OrderNotificationEntity extends AbstractTimestampableEntity
{
    public const NOTIFICATION_TYPE_ORDER_APPROVED = 'order_approved';

    public const NOTIFICATION_TYPE_ORDER_DECLINED = 'order_declined';

    public const NOTIFICATION_TYPE_PAYMENT = 'payment';

    public const NOTIFICATION_TYPE_DCI_COMMUNICATION = 'dci_communication';

    public const NOTIFICATION_TYPE_ORDER_WAITING = 'order_waiting';

    public const NOTIFICATION_TYPE_ORDER_SHIPPED = 'order_shipped';

    public const NOTIFICATION_TYPE_ORDER_PAID_OUT = 'order_paid_out';

    public const NOTIFICATION_TYPE_ORDER_LATE = 'order_late';

    public const NOTIFICATION_TYPE_ORDER_CANCELED = 'order_canceled';

    public const NOTIFICATION_TYPE_INVOICE_CANCELED = 'invoice_canceled';

    public const NOTIFICATION_TYPE_INVOICE_LATE = 'invoice_late';

    public const NOTIFICATION_TYPE_INVOICE_PAID_OUT = 'invoice_paid_out';

    private $orderId;

    private $invoiceUuid;

    private $notificationType;

    private $payload;

    private $isDelivered;

    private $deliveries;

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(?int $orderId): OrderNotificationEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getInvoiceUuid(): ?string
    {
        return $this->invoiceUuid;
    }

    public function setInvoiceUuid(?string $invoiceUuid): OrderNotificationEntity
    {
        $this->invoiceUuid = $invoiceUuid;

        return $this;
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function setNotificationType(string $notificationType): OrderNotificationEntity
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): OrderNotificationEntity
    {
        $this->payload = $payload;

        return $this;
    }

    public function isDelivered(): bool
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(bool $isDelivered): OrderNotificationEntity
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    /**
     * @return array|OrderNotificationDeliveryEntity[]
     */
    public function getDeliveries(): array
    {
        return $this->deliveries;
    }

    public function setDeliveries(array $deliveries): OrderNotificationEntity
    {
        $this->deliveries = $deliveries;

        return $this;
    }

    public function addDelivery(OrderNotificationDeliveryEntity $delivery): OrderNotificationEntity
    {
        $this->deliveries[] = $delivery;

        return $this;
    }
}
