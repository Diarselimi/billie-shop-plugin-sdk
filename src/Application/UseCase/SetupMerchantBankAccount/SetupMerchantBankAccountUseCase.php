<?php

declare(strict_types=1);

namespace App\Application\UseCase\SetupMerchantBankAccount;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\BankAccount\BankAccountCoreAcceptedAnnouncer;
use App\DomainModel\BankAccount\BankAccountDTO;
use App\DomainModel\BankAccount\BankAccountDTOFactory;
use App\DomainModel\BankAccount\BicLookupServiceRequestException;
use App\DomainModel\BankAccount\BicNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;

class SetupMerchantBankAccountUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $stepRepository;

    private $bankAccountDTOFactory;

    private $merchantRepository;

    private $announcer;

    private $stepTransitionService;

    public function __construct(
        MerchantOnboardingStepRepositoryInterface $stepRepository,
        BankAccountDTOFactory $bankAccountDTOFactory,
        MerchantRepositoryInterface $merchantRepository,
        BankAccountCoreAcceptedAnnouncer $announcer,
        MerchantStepTransitionService $stepTransitionService
    ) {
        $this->stepRepository = $stepRepository;
        $this->bankAccountDTOFactory = $bankAccountDTOFactory;
        $this->merchantRepository = $merchantRepository;
        $this->announcer = $announcer;
        $this->stepTransitionService = $stepTransitionService;
    }

    public function execute(SetupMerchantBankAccountRequest $request): void
    {
        $this->validateRequest($request);

        $step = $this->stepRepository->getOneByStepNameAndMerchant(
            MerchantOnboardingStepEntity::STEP_SEPA_MANDATE_CONFIRMATION,
            $request->getMerchantId()
        );

        if (!$step || $step->getState() !== MerchantOnboardingStepEntity::STATE_NEW) {
            throw new SetupMerchantBankAccountException();
        }

        $bankAccount = $this->buildBankAccountDTO($request);
        $this->announcer->announce($bankAccount);
        $this->stepTransitionService->transitionStepEntity($step, MerchantOnboardingStepTransitionEntity::TRANSITION_REQUEST_CONFIRMATION);
    }

    private function buildBankAccountDTO(SetupMerchantBankAccountRequest $request): BankAccountDTO
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        try {
            return $this->bankAccountDTOFactory->create(
                $merchant->getName(),
                $request->getIban(),
                $merchant->getPaymentUuid()
            );
        } catch (BicNotFoundException | BicLookupServiceRequestException $exception) {
            throw new SetupMerchantBankAccountMissingBicException();
        }
    }
}
