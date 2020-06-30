<?php

declare(strict_types=1);

namespace spec\App\DomainModel\IdentityVerification;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use PhpSpec\ObjectBehavior;

class IdentityVerificationSucceederSpec extends ObjectBehavior
{
    public function let(
        MerchantUserRepositoryInterface $userRepository,
        MerchantStepTransitionService $transitionService
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_throw_exception_when_user_not_found(MerchantUserRepositoryInterface $userRepository)
    {
        $caseUuid = 'aaa';
        $userRepository->getOneByIdentityVerificationCaseUuid($caseUuid)->willReturn(null);

        $this
            ->shouldThrow(MerchantUserNotFoundException::class)
            ->during('succeedIdentifcationVerification', [$caseUuid]);
    }

    public function it_should_transition_when_user_found(
        MerchantUserRepositoryInterface $userRepository,
        MerchantStepTransitionService $transitionService
    ) {
        // Arrange
        $caseUuid = 'aaa';
        $merchantId = 5;
        $merchantUser = new MerchantUserEntity();
        $merchantUser->setMerchantId($merchantId);
        $userRepository->getOneByIdentityVerificationCaseUuid($caseUuid)->willReturn($merchantUser);

        // Assert
        $transitionService->transition(
            MerchantOnboardingStepEntity::STEP_IDENTITY_VERIFICATION,
            MerchantOnboardingStepTransitionEntity::TRANSITION_COMPLETE,
            $merchantId
        )->shouldBeCalledOnce();

        // Act
        $this->succeedIdentifcationVerification($caseUuid);
    }
}
