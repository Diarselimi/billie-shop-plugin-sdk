<?php

declare(strict_types=1);

namespace App\DomainEvent\AnalyticsEvent;

use App\DomainModel\ArrayableInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractAnalyticsEvent extends Event implements ArrayableInterface
{
    private $identifierId;

    public function __construct(string $identifierId)
    {
        $this->identifierId = $identifierId;
    }

    abstract public function getEventType(): string;

    public function getIdentifierId(): string
    {
        return $this->identifierId;
    }
}
