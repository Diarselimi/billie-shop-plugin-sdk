<?php

namespace App\Application\UseCase\CheckoutCreateSession;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;

class CheckoutCreateSessionResponse implements ArrayableInterface
{
    private $sessionId;

    public function __construct(CheckoutSessionEntity $entity)
    {
        $this->sessionId = $entity->getUuid();
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function toArray(): array
    {
        return ['id' => $this->getSessionId()];
    }
}
