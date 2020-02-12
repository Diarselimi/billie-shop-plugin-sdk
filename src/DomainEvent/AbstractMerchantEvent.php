<?php

declare(strict_types=1);

namespace App\DomainEvent;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractMerchantEvent extends Event
{
    private $merchantId;

    public function __construct(int $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    abstract public function getTrackingEventName(): string;
}
