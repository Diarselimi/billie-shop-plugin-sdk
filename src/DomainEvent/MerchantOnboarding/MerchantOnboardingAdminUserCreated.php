<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingAdminUserCreated extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_RE_user_created';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
