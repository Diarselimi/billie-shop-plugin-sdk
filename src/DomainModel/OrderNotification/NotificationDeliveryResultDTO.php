<?php

namespace App\DomainModel\OrderNotification;

class NotificationDeliveryResultDTO
{
    private $responseCode;

    private $responseBody;

    public function __construct(int $responseCode, ?string $responseBody)
    {
        $this->responseCode = $responseCode;
        $this->responseBody = $responseBody;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function setResponseCode(int $responseCode): NotificationDeliveryResultDTO
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    public function getResponseBody(): ? string
    {
        return $this->responseBody;
    }

    public function setResponseBody(?string $responseBody): NotificationDeliveryResultDTO
    {
        $this->responseBody = $responseBody;

        return $this;
    }
}
