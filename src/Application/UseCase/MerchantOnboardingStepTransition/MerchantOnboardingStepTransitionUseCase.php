<?php

declare(strict_types=1);

namespace App\Application\UseCase\MerchantOnboardingStepTransition;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use Symfony\Component\Workflow\Workflow;

class MerchantOnboardingStepTransitionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $workflow;

    private $repository;

    public function __construct(Workflow $onboardingStepWorkflow, MerchantOnboardingStepRepositoryInterface $repository)
    {
        $this->workflow = $onboardingStepWorkflow;
        $this->repository = $repository;
    }

    public function execute(MerchantOnboardingStepTransitionRequest $request): void
    {
        $this->validateRequest($request);

        $step = $this->repository->getOneByNameAndMerchant($request->getStep(), $request->getMerchantPaymentUuid());

        if (!$step) {
            throw new MerchantOnboardingStepNotFoundException();
        }

        if (!$this->workflow->can($step, $request->getTransition())) {
            throw new WorkflowException("Onboarding Step Transition '{$request->getTransition()}' is not supported.");
        }

        $this->repository->update($step);
    }
}
