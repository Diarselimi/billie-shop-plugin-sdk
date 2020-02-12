<?php

declare(strict_types=1);

namespace App\Application\UseCase\MerchantOnboardingTransition;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingCompleted;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingNotFoundException;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class MerchantOnboardingTransitionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $workflow;

    private $repository;

    private $stepRepository;

    private $eventDispatcher;

    public function __construct(
        Workflow $onboardingWorkflow,
        MerchantOnboardingRepositoryInterface $repository,
        MerchantOnboardingStepRepositoryInterface $stepRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->workflow = $onboardingWorkflow;
        $this->repository = $repository;
        $this->stepRepository = $stepRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function execute(MerchantOnboardingTransitionRequest $request): void
    {
        $this->validateRequest($request);

        $onboarding = $this->repository->findNewestByPaymentUuid($request->getMerchantPaymentUuid());

        if (!$onboarding) {
            throw new MerchantOnboardingNotFoundException();
        }

        if ($request->getTransition() === MerchantOnboardingTransitionEntity::TRANSITION_COMPLETE) {
            if ($this->allStepsAreCompleted($onboarding)) {
                $this->eventDispatcher->dispatch(new MerchantOnboardingCompleted($onboarding->getMerchantId()));
            } else {
                throw new MerchantOnboardingStepsIncompleteException();
            }
        }

        if (!$this->workflow->can($onboarding, $request->getTransition())) {
            throw new WorkflowException("Onboarding Transition '{$request->getTransition()}' is not supported.");
        }

        $this->workflow->apply($onboarding, $request->getTransition());
        $this->repository->update($onboarding);
    }

    private function allStepsAreCompleted(MerchantOnboardingEntity $onboarding): bool
    {
        $steps = $this->stepRepository->findByMerchantOnboardingId($onboarding->getId(), true);
        foreach ($steps as $step) {
            if (!$step->isComplete()) {
                return false;
            }
        }

        return true;
    }
}
