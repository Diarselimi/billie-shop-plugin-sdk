<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantOnboarding;

use App\Application\Exception\WorkflowException;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingFinancialAssessmentConfirmed;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingIdentityVerificationConfirmed;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingSalesConfirmed;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingSepaMandateConfirmed;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingSignatoryPowerConfirmed;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingTechnicalIntegrationConfirmed;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingUboPepSanctionsConfirmed;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class MerchantStepTransitionService
{
    private const STEP_TRANSITION_EVENTS = [
        MerchantOnboardingStepEntity::STEP_FINANCIAL_ASSESSMENT => MerchantOnboardingFinancialAssessmentConfirmed::class,
        MerchantOnboardingStepEntity::STEP_SIGNATORY_CONFIRMATION => MerchantOnboardingSignatoryPowerConfirmed::class,
        MerchantOnboardingStepEntity::STEP_IDENTITY_VERIFICATION => MerchantOnboardingIdentityVerificationConfirmed::class,
        MerchantOnboardingStepEntity::STEP_UBO_PEPSANCTIONS_ASSESSMENT => MerchantOnboardingUboPepSanctionsConfirmed::class,
        MerchantOnboardingStepEntity::STEP_TECHNICAL_INTEGRATION => MerchantOnboardingTechnicalIntegrationConfirmed::class,
        MerchantOnboardingStepEntity::STEP_SEPA_MANDATE_CONFIRMATION => MerchantOnboardingSepaMandateConfirmed::class,
        MerchantOnboardingStepEntity::STEP_SALES_CONFIRMATION => MerchantOnboardingSalesConfirmed::class,
    ];

    private $repository;

    private $workflow;

    private $eventDispatcher;

    public function __construct(
        Workflow $onboardingStepWorkflow,
        MerchantOnboardingStepRepositoryInterface $repository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository = $repository;
        $this->workflow = $onboardingStepWorkflow;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws MerchantOnboardingStepNotFoundException
     */
    public function transition(string $stepName, string $transitionName, int $merchantId): void
    {
        $step = $this->repository->getOneByStepNameAndMerchant($stepName, $merchantId);

        if (!$step) {
            throw new MerchantOnboardingStepNotFoundException();
        }

        if ($transitionName === MerchantOnboardingStepTransitionEntity::TRANSITION_COMPLETE && $step->isComplete()) {
            return; // APIS-1905: ignore multiple transition to complete
        }

        $this->transitionStepEntity($step, $transitionName, $merchantId);
    }

    /**
     * @throws WorkflowException
     * @throws MerchantOnboardingStepNotFoundException
     */
    public function transitionStepEntity(MerchantOnboardingStepEntity $step, string $transitionName, int $merchantId): void
    {
        if (!$this->workflow->can($step, $transitionName)) {
            throw new WorkflowException(
                "Onboarding Step {$step->getName()} in state '{$step->getState()}' cannot be transitioned to '{$transitionName}'."
            );
        }
        $this->workflow->apply($step, $transitionName);
        $this->repository->update($step);
        $this->dispatchTransitionEvent($step, $merchantId, $transitionName);
    }

    /**
     * @throws MerchantOnboardingStepNotFoundException
     */
    private function dispatchTransitionEvent(MerchantOnboardingStepEntity $step, int $merchantId, string $transitionName): void
    {
        if (isset(self::STEP_TRANSITION_EVENTS[$step->getName()])) {
            $eventClass = self::STEP_TRANSITION_EVENTS[$step->getName()];
            $this->eventDispatcher->dispatch(new $eventClass($merchantId, $transitionName));
        } else {
            throw new MerchantOnboardingStepNotFoundException(
                "Onboarding Step {$step->getName()} not found for merchant {$merchantId}."
            );
        }
    }
}
