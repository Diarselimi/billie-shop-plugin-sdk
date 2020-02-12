<?php

namespace App\DomainEvent\MerchantOnboarding;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\TrackingAnalytics\TrackingAnalyticsServiceInterface;
use App\Http\Authentication\UserProvider;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MerchantOnboardingEventSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private $segmentIOClient;

    private $userUuid;

    public function __construct(TrackingAnalyticsServiceInterface $segmentIOClient, UserProvider $userProvider)
    {
        $this->segmentIOClient = $segmentIOClient;
        if ($userProvider->getMerchantUser()) {
            $this->userUuid = $userProvider->getMerchantUser()->getUserEntity()->getUuid();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            MerchantOnboardingCompleted::class => 'onMerchantOnboardingEvent',
            MerchantOnboardingAdminInvited::class => 'onMerchantOnboardingEvent',
            MerchantOnboardingAdminInvitationOpened::class => 'onMerchantOnboardingEvent',
            MerchantOnboardingAdminUserCreated::class => 'onMerchantOnboardingEvent',
            MerchantOnboardingFinancialAssessmentConfirmed::class => 'onMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingIdentityVerificationConfirmed::class => 'onMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingSalesConfirmed::class => 'onMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingSepaMandateConfirmed::class => 'onMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingSignatoryPowerConfirmed::class => 'onMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingTechnicalIntegrationConfirmed::class => 'onMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingUboPepSanctionsConfirmed::class => 'onMerchantOnboardingStepCompleteEvent',
        ];
    }

    public function onMerchantOnboardingEvent(AbstractMerchantOnboardingEvent $event)
    {
        $this->segmentIOClient->track($event->getTrackingEventName(), $event->getMerchantId(), ['id' => $this->userUuid]);
    }

    public function onMerchantOnboardingStepCompleteEvent(AbstractMerchantOnboardingEvent $event)
    {
        if ($event->getTransitionName() === MerchantOnboardingStepTransitionEntity::TRANSITION_COMPLETE) {
            $this->onMerchantOnboardingEvent($event);
        }
    }
}
