<?php

declare(strict_types=1);

namespace App\Application\UseCase\MerchantOnboardingTransition;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use Symfony\Component\Workflow\Workflow;

class MerchantOnboardingTransitionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $workflow;

    private $repository;

    private $stepRepository;

    public function __construct(
        Workflow $onboardingWorkflow,
        MerchantOnboardingRepositoryInterface $repository,
        MerchantOnboardingStepRepositoryInterface $stepRepository
    ) {
        $this->workflow = $onboardingWorkflow;
        $this->repository = $repository;
        $this->stepRepository = $stepRepository;
    }

    public function execute(MerchantOnboardingTransitionRequest $request): void
    {
        $this->validateRequest($request);

        $onboarding = $this->repository->findNewestByPaymentUuid($request->getMerchantPaymentUuid());

        if (!$onboarding) {
            throw new MerchantOnboardingNotFoundException();
        }

        $steps = $this->stepRepository->findByMerchantOnboardingId($onboarding->getId());
        foreach ($steps as $step) {
            if (!$step->isComplete()) {
                throw new MerchantOnboardingStepsIncompleteException();
            }
        }

        if (!$this->workflow->can($onboarding, $request->getTransition())) {
            throw new WorkflowException("Onboarding Transition '{$request->getTransition()}' is not supported.");
        }

        $this->workflow->apply($onboarding, $request->getTransition());
        $this->repository->update($onboarding);
    }
}
