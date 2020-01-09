<?php

declare(strict_types=1);

namespace App\Application\UseCase\SaveMerchantFinancialAssessment;

use App\Application\Exception\MerchantOnboardingStepTransitionException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentEntityFactory;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;

class SaveMerchantFinancialAssessmentUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $repository;

    private $entityFactory;

    private $stepTransitionService;

    public function __construct(
        MerchantFinancialAssessmentRepositoryInterface $repository,
        MerchantFinancialAssessmentEntityFactory $entityFactory,
        MerchantStepTransitionService $stepTransitionService
    ) {
        $this->repository = $repository;
        $this->entityFactory = $entityFactory;
        $this->stepTransitionService = $stepTransitionService;
    }

    public function execute(SaveMerchantFinancialAssessmentRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $this->stepTransitionService->transition(
                MerchantOnboardingStepEntity::STEP_FINANCIAL_ASSESSMENT,
                MerchantOnboardingStepTransitionEntity::TRANSITION_REQUEST_CONFIRMATION,
                $request->getMerchantPaymentUuid()
            );
        } catch (MerchantOnboardingStepNotFoundException | WorkflowException $exception) {
            throw new MerchantOnboardingStepTransitionException();
        }

        $entity = $this->entityFactory->createFromDataAndMerchant($request->toArray(), $request->getMerchantId());
        $this->repository->insert($entity);
    }
}
