<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateMerchantOnboardingStepState;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;

class UpdateMerchantOnboardingStepStateUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    public function __construct()
    {
    }

    public function execute(UpdateMerchantOnboardingStepStateRequest $request): void
    {
        $this->validateRequest($request);

        //TODO: implement logic
    }
}
