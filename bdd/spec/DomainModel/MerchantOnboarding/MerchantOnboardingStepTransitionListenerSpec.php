<?php

declare(strict_types=1);

namespace spec\App\DomainModel\MerchantOnboarding;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionRepositoryInterface;
use Ozean12\Transfer\Message\MerchantOnboarding\MerchantOnboardingStepTransition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;

class MerchantOnboardingStepTransitionListenerSpec extends ObjectBehavior
{
    private const STEP_ID = 1;

    private const MERCHANT_ONBOARDING_ID = 3;

    public function let(
        MessageBusInterface $bus,
        MerchantOnboardingStepTransitionRepositoryInterface $transitionRepository,
        MerchantRepository $merchantRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_dispatches_message(
        MessageBusInterface $bus,
        MerchantOnboardingStepTransitionRepositoryInterface $transitionRepository,
        MerchantRepository $merchantRepository,
        Event $event,
        LoggerInterface $logger
    ): void {
        $event->getSubject()->willReturn($this->createStep());
        $from = MerchantOnboardingStepEntity::STATE_PENDING;
        $to = MerchantOnboardingStepEntity::STATE_COMPLETE;
        $transition = (new MerchantOnboardingStepTransitionEntity())
            ->setFrom($from)
            ->setTo($to);
        $transitionRepository->findNewestByStepId(self::STEP_ID)->willReturn($transition);
        $merchantPaymentUuid = Uuid::uuid4()->toString();
        $merchantId = 2;
        $merchant = (new MerchantEntity())
            ->setId($merchantId)
            ->setPaymentUuid($merchantPaymentUuid);
        $merchantRepository
            ->getOneByMerchantOnboardingId(self::MERCHANT_ONBOARDING_ID)
            ->willReturn($merchant);

        $bus->dispatch(Argument::that(
            function (MerchantOnboardingStepTransition $message) use (
                $merchantPaymentUuid,
                $from,
                $to
            ) {
                return $message->getMerchantPaymentUuid() === $merchantPaymentUuid
                    && $message->getTransitionStepName() === MerchantOnboardingStepEntity::STEP_IDENTITY_VERIFICATION
                    && $message->getTransitionFrom() === $from
                    && $message->getTransitionTo() === $to
                    && preg_match('/\d{4}-\d{2}-\d{2}/', $message->getTransitionDate())
                    ;
            }
        ))->shouldBeCalledOnce()->willReturn(new Envelope(new MerchantOnboardingStepTransition()));
        $logger->info(Argument::any(), [])->shouldBeCalledOnce();

        $this->onCompleted($event);
    }

    public function it_logs_error_when_no_transition_found(
        MerchantOnboardingStepTransitionRepositoryInterface $transitionRepository,
        Event $event,
        LoggerInterface $logger,
        MessageBusInterface $bus
    ): void {
        $event->getSubject()->willReturn($this->createStep());
        $transitionRepository->findNewestByStepId(self::STEP_ID)->willReturn(null);

        $logger->error(Argument::cetera())->shouldBeCalledOnce();
        $bus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->onCompleted($event);
    }

    public function it_logs_error_when_no_merchant_found(
        MerchantOnboardingStepTransitionRepositoryInterface $transitionRepository,
        MerchantRepository $merchantRepository,
        Event $event,
        LoggerInterface $logger,
        MessageBusInterface $bus
    ): void {
        $event->getSubject()->willReturn($this->createStep());
        $transitionRepository
            ->findNewestByStepId(self::STEP_ID)
            ->willReturn(new MerchantOnboardingStepTransitionEntity());
        $merchantRepository
            ->getOneByMerchantOnboardingId(self::MERCHANT_ONBOARDING_ID)
            ->willReturn(null);

        $logger->error(Argument::cetera())->shouldBeCalledOnce();
        $bus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->onCompleted($event);
    }

    private function createStep(): MerchantOnboardingStepEntity
    {
        return (new MerchantOnboardingStepEntity())
            ->setId(self::STEP_ID)
            ->setName(MerchantOnboardingStepEntity::STEP_IDENTITY_VERIFICATION)
            ->setMerchantOnboardingId(self::MERCHANT_ONBOARDING_ID);
    }
}
