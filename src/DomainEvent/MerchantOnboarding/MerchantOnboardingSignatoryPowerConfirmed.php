<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingSignatoryPowerConfirmed extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_SI_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
