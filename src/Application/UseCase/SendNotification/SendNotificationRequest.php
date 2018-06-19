<?php

namespace App\Application\UseCase\SendNotification;

class SendNotificationRequest
{
    private $eventName;
    private $orderId;
    private $amount;
    private $openAmount;
    private $urlNotification;

    public function setEventName(?string $eventName): SendNotificationRequest
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setOrderId(?string $orderId): SendNotificationRequest
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setAmount(?float $amount): SendNotificationRequest
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setOpenAmount(?float $openAmount): SendNotificationRequest
    {
        $this->openAmount = $openAmount;

        return $this;
    }

    public function getOpenAmount(): ?float
    {
        return $this->openAmount;
    }

    public function setUrlNotification(?string $urlNotification): SendNotificationRequest
    {
        $this->urlNotification = $urlNotification;

        return $this;
    }

    public function getUrlNotification(): ?string
    {
        return $this->urlNotification;
    }
}
