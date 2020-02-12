<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingIdentityVerificationConfirmed extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_ID_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
