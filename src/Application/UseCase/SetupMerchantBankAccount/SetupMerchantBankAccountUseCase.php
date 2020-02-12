<?php

declare(strict_types=1);

namespace App\Application\UseCase\SetupMerchantBankAccount;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\BankAccount\BankAccountCoreAcceptedAnnouncer;
use App\DomainModel\BankAccount\BankAccountDTOFactory;
use App\DomainModel\BankAccount\BicLookupServiceInterface;
use App\DomainModel\BankAccount\BicLookupServiceRequestException;
use App\DomainModel\BankAccount\BicNotFoundException;
use App\DomainModel\BankAccount\IbanDTOFactory;
use App\DomainModel\BankAccount\InvalidIbanException;
use App\DomainModel\BankAccount\SepaMandateReferenceGenerator;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\Infrastructure\SepaB2BGenerator\SepaB2BGeneratorService;

class SetupMerchantBankAccountUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $stepRepository;

    private $bankAccountDTOFactory;

    private $merchantRepository;

    private $announcer;

    private $stepTransitionService;

    private $sepaB2BGeneratorService;

    private $ibanDTOFactory;

    private $bicLookupService;

    private $mandateReferenceGenerator;

    public function __construct(
        MerchantOnboardingStepRepositoryInterface $stepRepository,
        BankAccountDTOFactory $bankAccountDTOFactory,
        MerchantRepositoryInterface $merchantRepository,
        BankAccountCoreAcceptedAnnouncer $announcer,
        MerchantStepTransitionService $stepTransitionService,
        SepaB2BGeneratorService $sepaB2BGeneratorService,
        IbanDTOFactory $ibanDTOFactory,
        BicLookupServiceInterface $bicLookupService,
        SepaMandateReferenceGenerator $mandateReferenceGenerator
    ) {
        $this->stepRepository = $stepRepository;
        $this->bankAccountDTOFactory = $bankAccountDTOFactory;
        $this->merchantRepository = $merchantRepository;
        $this->announcer = $announcer;
        $this->stepTransitionService = $stepTransitionService;
        $this->sepaB2BGeneratorService = $sepaB2BGeneratorService;
        $this->ibanDTOFactory = $ibanDTOFactory;
        $this->bicLookupService = $bicLookupService;
        $this->mandateReferenceGenerator = $mandateReferenceGenerator;
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

        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        try {
            $iban = $this->ibanDTOFactory->createFromString($request->getIban());
            $bankData = $this->bicLookupService->lookup($iban);
        } catch (InvalidIbanException | BicNotFoundException | BicLookupServiceRequestException $exception) {
            throw new SetupMerchantBankAccountMissingBicException();
        }

        $this->stepTransitionService->transition(
            MerchantOnboardingStepEntity::STEP_SEPA_MANDATE_CONFIRMATION,
            MerchantOnboardingStepTransitionEntity::TRANSITION_REQUEST_CONFIRMATION,
            $merchant->getId()
        );

        $bankAccount = $this->bankAccountDTOFactory->create(
            $merchant->getName(),
            $iban,
            $bankData->getBic(),
            $merchant->getPaymentUuid()
        );

        $mandateReference = $this->mandateReferenceGenerator->generate();
        $this->announcer->announce($bankAccount, $mandateReference);
        $fileResponseDTO = $this->sepaB2BGeneratorService->generate($merchant->getCompanyUuid(), $bankAccount, $bankData, $mandateReference);

        $merchant->setSepaB2BDocumentUuid($fileResponseDTO->getUuid());
        $this->merchantRepository->update($merchant);
    }
}
