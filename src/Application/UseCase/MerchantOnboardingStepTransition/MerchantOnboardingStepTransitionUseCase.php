<?php

declare(strict_types=1);

namespace App\Application\UseCase\MerchantOnboardingStepTransition;

use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;

class MerchantOnboardingStepTransitionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $stepTransitionService;

    private $merchantRepository;

    public function __construct(MerchantStepTransitionService $stepTransitionService, MerchantRepository $merchantRepository)
    {
        $this->stepTransitionService = $stepTransitionService;
        $this->merchantRepository = $merchantRepository;
    }

    public function execute(MerchantOnboardingStepTransitionRequest $request): void
    {
        $this->validateRequest($request);

        $merchant = $this->merchantRepository->getOneByUuid($request->getMerchantPaymentUuid());
        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        $this
            ->stepTransitionService
            ->transition(
                $request->getStep(),
                $request->getTransition(),
                $merchant->getId()
            );
    }
}
