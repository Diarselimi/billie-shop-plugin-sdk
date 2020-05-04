<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantIntegrationStarted extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_IN_started';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
