<?php

namespace App\DomainModel\Webhook;

class NotificationDTO
{
    const EVENT_PAYMENT = 'payment';

    private $eventName;

    private $orderId;

    private $amount;

    private $openAmount;

    private $urlNotification;

    public function setEventName(string $eventName): NotificationDTO
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setOrderId(string $orderId): NotificationDTO
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setAmount(?float $amount): NotificationDTO
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setOpenAmount(?float $openAmount): NotificationDTO
    {
        $this->openAmount = $openAmount;

        return $this;
    }

    public function getOpenAmount(): ?float
    {
        return $this->openAmount;
    }

    public function setUrlNotification(?string $urlNotification): NotificationDTO
    {
        $this->urlNotification = $urlNotification;

        return $this;
    }

    public function getUrlNotification(): ?string
    {
        return $this->urlNotification;
    }

    public function isEventTypePayment(): bool
    {
        return $this->eventName === self::EVENT_PAYMENT;
    }
}
