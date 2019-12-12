<?php

declare(strict_types=1);

namespace App\Application\UseCase\MerchantFinishIntegration;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;

class MerchantFinishIntegrationUseCase
{
    private $stepTransitionerService;

    public function __construct(MerchantStepTransitionService $stepTransitionerService)
    {
        $this->stepTransitionerService = $stepTransitionerService;
    }

    /**
     * @throws MerchantOnboardingStepNotFoundException
     */
    public function execute(MerchantFinishIntegrationRequest $request): void
    {
        $this
            ->stepTransitionerService
            ->transition(
                $request->getStepName(),
                $request->getTransitionName(),
                $request->getMerchantPaymentUuid()
            );
    }
}
