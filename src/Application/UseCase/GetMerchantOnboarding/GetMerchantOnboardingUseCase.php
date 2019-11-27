<?php

namespace App\Application\UseCase\GetMerchantOnboarding;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainerFactory;

class GetMerchantOnboardingUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $onboardingContainerFactory;

    public function __construct(MerchantOnboardingContainerFactory $onboardingContainerFactory)
    {
        $this->onboardingContainerFactory = $onboardingContainerFactory;
    }

    public function execute(GetMerchantOnboardingRequest $request): GetMerchantOnboardingResponse
    {
        $this->validateRequest($request);

        $onboardingContainer = $this->onboardingContainerFactory->create($request->getMerchantId());

        return new GetMerchantOnboardingResponse(
            $onboardingContainer->getOnboarding()->getState(),
            ... $onboardingContainer->getOnboardingSteps()
        );
    }
}
