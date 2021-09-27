<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Application\Tracking\TrackingEventCollector;
use App\DomainModel\TrackingAnalytics\TrackingAnalyticsServiceInterface;
use Composer\EventDispatcher\EventSubscriberInterface;

class TrackingEventSubscriber implements EventSubscriberInterface
{
    private TrackingEventCollector $collector;

    private TrackingAnalyticsServiceInterface $client;

    public function __construct(TrackingEventCollector $collector, TrackingAnalyticsServiceInterface $client)
    {
        $this->collector = $collector;
        $this->client = $client;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.terminate' => 'sendTrackingEvents',
        ];
    }

    public function sendTrackingEvents(): void
    {
        foreach ($this->collector->getEvents() as $event) {
            $this->client->trackEvent($event);
        }
    }
}
