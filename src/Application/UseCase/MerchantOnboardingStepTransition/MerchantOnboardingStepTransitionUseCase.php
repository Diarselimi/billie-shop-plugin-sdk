<?php

declare(strict_types=1);

namespace App\Application\UseCase\MerchantOnboardingStepTransition;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;

class MerchantOnboardingStepTransitionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $stepTransitionerService;

    public function __construct(MerchantStepTransitionService $stepTransitionService)
    {
        $this->stepTransitionerService = $stepTransitionService;
    }

    public function execute(MerchantOnboardingStepTransitionRequest $request): void
    {
        $this->validateRequest($request);

        $this
            ->stepTransitionerService
            ->transition(
                $request->getStep(),
                $request->getTransition(),
                $request->getMerchantPaymentUuid()
            );
    }
}
