<?php

namespace App\Application\UseCase\SignatoryPowersSelection;

use App\Application\Exception\MerchantOnboardingStepTransitionException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\SignatoryPower\SignatoryPowerAlreadySignedException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class SignatoryPowersSelectionUseCase implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait, LoggingTrait;

    private $companiesService;

    private $stepTransitionService;

    private $merchantUserRepository;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantStepTransitionService $stepTransitionService
    ) {
        $this->companiesService = $companiesService;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->stepTransitionService = $stepTransitionService;
    }

    public function execute(SignatoryPowersSelectionRequest $selectionsRequest): void
    {
        $this->validateRequest($selectionsRequest);

        try {
            $this->stepTransitionService->transition(
                MerchantOnboardingStepEntity::STEP_SIGNATORY_CONFIRMATION,
                MerchantOnboardingStepTransitionEntity::TRANSITION_REQUEST_CONFIRMATION,
                $selectionsRequest->getMerchantUser()->getMerchantId()
            );
        } catch (MerchantOnboardingStepNotFoundException | WorkflowException $exception) {
            throw new MerchantOnboardingStepTransitionException();
        }

        try {
            $this->companiesService->saveSelectedSignatoryPowers(
                $selectionsRequest->getCompanyId(),
                ...$selectionsRequest->getSignatoryPowers()
            );
        } catch (CompaniesServiceRequestException $exception) {
            throw new SignatoryPowersSelectionException();
        }

        $loggedInSignatory = $selectionsRequest->findSelectedAsLoggedInSignatory();
        if ($loggedInSignatory) {
            $this->merchantUserRepository->assignSignatoryPowerToUser(
                $selectionsRequest->getMerchantUser()->getId(),
                $loggedInSignatory->getUuid()
            );

            try {
                $this->companiesService->acceptSignatoryPowerTc($loggedInSignatory->getUuid());
            } catch (SignatoryPowerAlreadySignedException $exception) {
                $this->logWarning(
                    'Signatory already signed',
                    [LoggingInterface::KEY_UUID => $loggedInSignatory->getUuid()]
                );
            }

            if ($selectionsRequest->getMerchantUser()->getIdentityVerificationCaseUuid()) {
                $this->companiesService->assignIdentityVerificationCase(
                    $selectionsRequest->getMerchantUser()->getIdentityVerificationCaseUuid(),
                    $loggedInSignatory->getUuid()
                );
            }

            if (count($selectionsRequest->getSignatoryPowers()) === 1) {
                $this->stepTransitionService->transition(
                    MerchantOnboardingStepEntity::STEP_SIGNATORY_CONFIRMATION,
                    MerchantOnboardingStepTransitionEntity::TRANSITION_COMPLETE,
                    $selectionsRequest->getMerchantUser()->getMerchantId()
                );
            }
        }
    }
}
