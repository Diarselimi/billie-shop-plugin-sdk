<?php

namespace App\DomainEvent\MerchantOnboarding;

class MerchantOnboardingAdminInvitationOpened extends AbstractMerchantOnboardingEvent
{
    private const TRACKING_EVENT_NAME = 'ON_RE_invitation_opened';

    public function getTrackingEventName(): string
    {
        return self::TRACKING_EVENT_NAME;
    }
}
