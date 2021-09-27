<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Application\Tracking\TrackingEventCollector;
use App\DomainModel\TrackingAnalytics\TrackingAnalyticsServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
            'kernel.terminate' => 'sendCollectedTrackingEvents',
        ];
    }

    public function sendCollectedTrackingEvents(): void
    {
        foreach ($this->collector->getEvents() as $event) {
            $this->client->trackEvent($event);
        }
    }
}
