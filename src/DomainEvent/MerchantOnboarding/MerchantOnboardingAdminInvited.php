<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingAdminInvited extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_RE_merchant_invited';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
