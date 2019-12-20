<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantOnboarding;

use App\Application\Exception\WorkflowException;
use Symfony\Component\Workflow\Workflow;

class MerchantStepTransitionService
{
    private $repository;

    private $workflow;

    public function __construct(Workflow $onboardingStepWorkflow, MerchantOnboardingStepRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->workflow = $onboardingStepWorkflow;
    }

    /**
     * @throws MerchantOnboardingStepNotFoundException
     */
    public function transition(string $stepName, string $transitionName, string $merchantPaymentUuid): void
    {
        $step = $this->repository->getOneByStepNameAndPaymentUuid($stepName, $merchantPaymentUuid);

        if (!$step) {
            throw new MerchantOnboardingStepNotFoundException();
        }

        $this->transitionStepEntity($step, $transitionName);
    }

    /**
     * @throws WorkflowException
     */
    public function transitionStepEntity(MerchantOnboardingStepEntity $step, string $transitionName): void
    {
        if (!$this->workflow->can($step, $transitionName)) {
            throw new WorkflowException(
                "Onboarding Step {$step->getName()} in state '{$step->getState()}' cannot be transitioned to '{$transitionName}'."
            );
        }
        $this->workflow->apply($step, $transitionName);
        $this->repository->update($step);
    }
}
