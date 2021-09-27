<?php

declare(strict_types=1);

namespace App\Application\Tracking;

class TrackingEventCollector
{
    private array

 $events = [];

    public function collect(TrackingEvent $event)
    {
        $this->events[] = $event;
    }

    /**
     * @return TrackingEvent[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
