<?php

namespace spec\App\Application\UseCase\StartIdentityVerification;

use App\Application\UseCase\StartIdentityVerification\StartIdentityVerificationException;
use App\Application\UseCase\StartIdentityVerification\StartIdentityVerificationRequest;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceInterface;
use App\DomainModel\IdentityVerification\IdentityVerificationStartResponseDTO;
use App\DomainModel\IdentityVerification\StartIdentityVerificationGuard;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StartIdentityVerificationUseCaseSpec extends ObjectBehavior
{
    public function let(
        IdentityVerificationServiceInterface $identityVerificationService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantStepTransitionService $stepTransitionService,
        MerchantOnboardingStepRepositoryInterface $merchantOnboardingStepRepository,
        StartIdentityVerificationGuard $startIdentityVerificationGuard,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(...func_get_args());

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_should_throw_exception_when_step_not_found(
        MerchantOnboardingStepRepositoryInterface $merchantOnboardingStepRepository
    ): void {
        $merchantOnboardingStepRepository->getOneByStepNameAndMerchant(Argument::cetera())->willReturn(null);
        $request = new StartIdentityVerificationRequest(1, Uuid::uuid4()->toString());

        $this
            ->shouldThrow(MerchantOnboardingStepNotFoundException::class)
            ->during('execute', [$request]);
    }

    public function it_should_start_case_when_step_is_pending(
        IdentityVerificationServiceInterface $identityVerificationService,
        MerchantOnboardingStepRepositoryInterface $merchantOnboardingStepRepository,
        MerchantStepTransitionService $stepTransitionService,
        StartIdentityVerificationGuard $startIdentityVerificationGuard
    ): void {
        $step = (new MerchantOnboardingStepEntity())->setState(MerchantOnboardingStepEntity::STATE_PENDING);
        $merchantOnboardingStepRepository->getOneByStepNameAndMerchant(Argument::cetera())->willReturn($step);
        $request = $this->createStartRequest();
        $responseDto = (new IdentityVerificationStartResponseDTO())->setUrl('http://someUrl');
        $startIdentityVerificationGuard->startIdentityVerificationAllowed()->willReturn(true);

        $identityVerificationService
            ->startVerificationCase(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($responseDto);
        $stepTransitionService->transitionStepEntity(Argument::cetera())->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_should_start_case_when_step_is_new(
        IdentityVerificationServiceInterface $identityVerificationService,
        MerchantOnboardingStepRepositoryInterface $merchantOnboardingStepRepository,
        MerchantStepTransitionService $stepTransitionService
    ): void {
        $step = (new MerchantOnboardingStepEntity())->setState(MerchantOnboardingStepEntity::STATE_NEW);
        $merchantOnboardingStepRepository->getOneByStepNameAndMerchant(Argument::cetera())->willReturn($step);
        $request = $this->createStartRequest();
        $responseDto = (new IdentityVerificationStartResponseDTO())->setUrl('http://someUrl');

        $identityVerificationService
            ->startVerificationCase(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($responseDto);
        $stepTransitionService->transitionStepEntity(Argument::cetera())->shouldBeCalledOnce();

        $this->execute($request);
    }

    public function it_should_throw_exception_when_guard_disallows(
        IdentityVerificationServiceInterface $identityVerificationService,
        MerchantOnboardingStepRepositoryInterface $merchantOnboardingStepRepository,
        MerchantStepTransitionService $stepTransitionService,
        StartIdentityVerificationGuard $startIdentityVerificationGuard
    ): void {
        $step = (new MerchantOnboardingStepEntity())->setState(MerchantOnboardingStepEntity::STATE_PENDING);
        $merchantOnboardingStepRepository->getOneByStepNameAndMerchant(Argument::cetera())->willReturn($step);
        $request = $this->createStartRequest();
        $startIdentityVerificationGuard->startIdentityVerificationAllowed()->willReturn(false);

        $identityVerificationService->startVerificationCase(Argument::any())->shouldNotBeCalled();
        $stepTransitionService->transitionStepEntity(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(StartIdentityVerificationException::class)->during('execute', [$request]);
    }

    private function createStartRequest(): StartIdentityVerificationRequest
    {
        return (new StartIdentityVerificationRequest(1, Uuid::uuid4()->toString()))
            ->setMerchantUserId(1)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('john.doe@billie.io')
            ->setRedirectUrlCouponRequested('http://someUrl')
            ->setRedirectUrlReviewPending('http://someUrl')
            ->setRedirectUrlDeclined('http://someUrl');
    }
}
