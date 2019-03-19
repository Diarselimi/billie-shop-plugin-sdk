<?php

namespace App\DomainModel\OrderNotification;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class OrderNotificationDeliveryEntity extends AbstractTimestampableEntity
{
    private $orderNotificationId;

    private $url;

    private $responseCode;

    private $responseBody;

    public function getOrderNotificationId(): int
    {
        return $this->orderNotificationId;
    }

    public function setOrderNotificationId(int $orderNotificationId): OrderNotificationDeliveryEntity
    {
        $this->orderNotificationId = $orderNotificationId;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): OrderNotificationDeliveryEntity
    {
        $this->url = $url;

        return $this;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function setResponseCode(int $responseCode): OrderNotificationDeliveryEntity
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    public function getResponseBody(): ? string
    {
        return $this->responseBody;
    }

    public function setResponseBody(?string $responseBody): OrderNotificationDeliveryEntity
    {
        $this->responseBody = $responseBody;

        return $this;
    }

    public function isResponseCodeSuccessful(): bool
    {
        return $this->responseCode >= 200 && $this->responseCode < 300;
    }
}
