<?php

namespace App\Application\Tracking;

interface TrackingEvent
{
    public function getEventName(): string;

    public function getMerchantId(): int;

    public function getPayload(): array;
}
