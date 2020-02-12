<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingUboPepSanctionsConfirmed extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_UP_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
