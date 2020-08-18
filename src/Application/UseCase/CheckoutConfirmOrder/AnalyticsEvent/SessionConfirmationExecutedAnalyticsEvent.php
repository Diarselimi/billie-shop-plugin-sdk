<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder\AnalyticsEvent;

use App\DomainEvent\AnalyticsEvent\AbstractAnalyticsEvent;

class SessionConfirmationExecutedAnalyticsEvent extends AbstractAnalyticsEvent
{
    private const SESSION_CONFIRMATION = 'session_confirmation';

    private $request;

    private $response;

    private $statusCode;

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(string $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(string $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getEventType(): string
    {
        return self::SESSION_CONFIRMATION;
    }

    public function toArray(): array
    {
        return [
            'identifier_id' => $this->getIdentifierId(),
            'request' => $this->getRequest(),
            'response' => $this->getResponse(),
            'response_status_code' => $this->getStatusCode(),
            'event_type' => $this->getEventType(),
        ];
    }
}
