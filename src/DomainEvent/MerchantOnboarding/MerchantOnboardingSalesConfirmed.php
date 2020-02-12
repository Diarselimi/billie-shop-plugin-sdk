<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingSalesConfirmed extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_SC_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
