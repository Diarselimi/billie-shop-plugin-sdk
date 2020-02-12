<?php

declare(strict_types=1);

namespace App\DomainModel\TrackingAnalytics;

interface TrackingAnalyticsServiceInterface
{
    public function track(string $eventName, string $merchantId, array $payload = []): void;
}
