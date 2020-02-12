<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingCompleted extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_SUCCESS_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
