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
            MerchantOnboardingCompleted::class => 'trackMerchantOnboardingEvent',
            MerchantOnboardingAdminInvited::class => 'trackMerchantOnboardingEvent',
            MerchantOnboardingAdminInvitationOpened::class => 'trackMerchantOnboardingEvent',
            MerchantOnboardingAdminUserCreated::class => 'trackMerchantOnboardingEvent',
            MerchantOnboardingFinancialAssessmentConfirmed::class => 'trackMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingIdentityVerificationConfirmed::class => 'trackMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingSalesConfirmed::class => 'trackMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingSepaMandateConfirmed::class => 'trackMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingSignatoryPowerConfirmed::class => 'trackMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingTechnicalIntegrationConfirmed::class => 'trackMerchantOnboardingStepCompleteEvent',
            MerchantOnboardingUboPepSanctionsConfirmed::class => 'trackMerchantOnboardingStepCompleteEvent',
        ];
    }

    public function trackMerchantOnboardingEvent(AbstractMerchantOnboardingEvent $event)
    {
        $this->segmentIOClient->track($event->getTrackingEventName(), $event->getMerchantId(), ['id' => $this->userUuid]);
    }

    public function trackMerchantOnboardingStepCompleteEvent(AbstractMerchantOnboardingEvent $event)
    {
        if ($event->getTransitionName() === MerchantOnboardingStepTransitionEntity::TRANSITION_COMPLETE) {
            $this->trackMerchantOnboardingEvent($event);
        }
    }
}
