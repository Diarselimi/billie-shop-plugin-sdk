<?php

declare(strict_types=1);

namespace App\DomainModel\TrackingAnalytics;

use App\Application\Tracking\TrackingEvent;

interface TrackingAnalyticsServiceInterface
{
    public function track(string $eventName, string $merchantId, array $payload = []): void;

    public function trackEvent(TrackingEvent $event): void;
}
