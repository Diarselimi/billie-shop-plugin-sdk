<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingTechnicalIntegrationConfirmed extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_IN_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
