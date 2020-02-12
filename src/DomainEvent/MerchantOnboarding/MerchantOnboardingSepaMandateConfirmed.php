<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingSepaMandateConfirmed extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_BA_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
