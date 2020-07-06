<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantOnboarding;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Support\DateFormat;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\MerchantOnboarding\MerchantOnboardingStepTransition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;

class MerchantOnboardingStepTransitionListener implements LoggingInterface, EventSubscriberInterface
{
    use LoggingTrait;

    private $bus;

    private $transitionRepository;

    private $merchantRepository;

    public function __construct(
        MessageBusInterface $bus,
        MerchantOnboardingStepTransitionRepositoryInterface $transitionRepository,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->bus = $bus;
        $this->transitionRepository = $transitionRepository;
        $this->merchantRepository = $merchantRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return ['workflow.merchant_onboarding_step.completed' => 'onCompleted'];
    }

    public function onCompleted(Event $event): void
    {
        /** @var MerchantOnboardingStepEntity $step */
        $step = $event->getSubject();
        $lastTransition = $this->transitionRepository->findNewestByStepId($step->getId());
        if ($lastTransition === null) {
            $this->logError('No transition found for step to publish to queue', ['stepId' => $step->getId()]);

            return;
        }
        $merchant = $this->merchantRepository->getOneByMerchantOnboardingId($step->getMerchantOnboardingId());
        if ($merchant === null) {
            $this->logError(
                'No merchant found to publish transition to queue',
                ['onboardingId' => $step->getMerchantOnboardingId()]
            );

            return;
        }
        $this->dispatch($lastTransition, $step, $merchant);
    }

    private function dispatch(
        MerchantOnboardingStepTransitionEntity $transition,
        MerchantOnboardingStepEntity $step,
        MerchantEntity $merchant
    ): void {
        $message = (new MerchantOnboardingStepTransition())
            ->setMerchantPaymentUuid($merchant->getPaymentUuid())
            ->setTransitionStepName($step->getName())
            ->setTransitionFrom($transition->getFrom())
            ->setTransitionTo($transition->getTo())
            ->setTransitionDate(date(DateFormat::FORMAT_YMD));
        $this->bus->dispatch($message);
        $this->logInfo('MerchantOnboardingStepTransition event announced');
    }
}
