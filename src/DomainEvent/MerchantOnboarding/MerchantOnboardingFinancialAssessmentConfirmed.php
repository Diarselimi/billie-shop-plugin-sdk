<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingFinancialAssessmentConfirmed extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_FA_confirmed';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
