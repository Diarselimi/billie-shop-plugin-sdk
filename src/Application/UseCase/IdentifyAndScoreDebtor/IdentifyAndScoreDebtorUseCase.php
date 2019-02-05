<?php

namespace App\Application\UseCase\IdentifyAndScoreDebtor;

use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Infrastructure\Alfred\IsEligibleForPayAfterDeliveryRequestDTOFactory;

class IdentifyAndScoreDebtorUseCase
{
    private $merchantRepository;

    private $merchantSettingsRepository;

    private $merchantDebtorRepository;

    private $scoreThresholdsConfigurationRepository;

    private $merchantDebtorFactory;

    private $eligibleForPayAfterDeliveryRequestDTOFactory;

    private $companiesService;

    private $paymentService;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        MerchantDebtorEntityFactory $merchantDebtorFactory,
        IsEligibleForPayAfterDeliveryRequestDTOFactory $eligibleForPayAfterDeliveryRequestDTOFactory,
        AlfredInterface $companiesService,
        BorschtInterface $paymentService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->merchantDebtorFactory = $merchantDebtorFactory;
        $this->eligibleForPayAfterDeliveryRequestDTOFactory = $eligibleForPayAfterDeliveryRequestDTOFactory;
        $this->companiesService = $companiesService;
        $this->paymentService = $paymentService;
    }

    public function execute(IdentifyAndScoreDebtorRequest $request, bool $doScoring): IdentifyAndScoreDebtorResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        $identifiedDebtor = $this->companiesService->identifyDebtor([
            'name' => $request->getName(),
            'address_house' => $request->getAddressHouse(),
            'address_street' => $request->getAddressStreet(),
            'address_postal_code' => $request->getAddressPostalCode(),
            'address_city' => $request->getAddressCity(),
            'address_country' => $request->getAddressCountry(),
            'tax_id' => $request->getTaxId(),
            'tax_number' => $request->getTaxNumber(),
            'registration_number' => $request->getRegistrationNumber(),
            'registration_court' => $request->getRegistrationCourt(),
            'legal_form' => $request->getLegalForm(),
            'first_name' => $request->getFirstName(),
            'last_name' => $request->getLastName(),
        ]);

        if (!$identifiedDebtor) {
            throw new DebtorNotIdentifiedException();
        }

        $merchantSettings = $this->merchantSettingsRepository->getOneByMerchant($merchant->getId());

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndDebtorId(
            $merchant->getId(),
            $identifiedDebtor->getId()
        );

        if (!$merchantDebtor) {
            $merchantDebtor = $this->createMerchantDebtor($merchant, $merchantSettings, $identifiedDebtor->getId());
        }

        $isEligible = null;

        if ($doScoring) {
            $isEligible = $this->isEligible($merchantSettings, $merchantDebtor);
        }

        return new IdentifyAndScoreDebtorResponse($merchantDebtor->getDebtorId(), $isEligible);
    }

    private function createMerchantDebtor(
        MerchantEntity $merchant,
        MerchantSettingsEntity $merchantSettings,
        string $debtorId
    ): MerchantDebtorEntity {
        $paymentDebtorId = $this->paymentService->registerDebtor($merchant->getPaymentMerchantId())->getPaymentDebtorId();

        $merchantDebtor = $this->merchantDebtorFactory->create(
            $debtorId,
            $merchant->getId(),
            $paymentDebtorId,
            $merchantSettings->getDebtorFinancingLimit()
        );

        $this->merchantDebtorRepository->insert($merchantDebtor);

        return $merchantDebtor;
    }

    private function isEligible(MerchantSettingsEntity $merchantSettings, MerchantDebtorEntity $merchantDebtor)
    {
        $merchantScoreThresholds = $this->scoreThresholdsConfigurationRepository
            ->getById($merchantSettings->getScoreThresholdsConfigurationId());

        $debtorScoreThresholds = ($merchantDebtor->getScoreThresholdsConfigurationId()) ?
            $this->scoreThresholdsConfigurationRepository->getById($merchantDebtor->getScoreThresholdsConfigurationId())
            : null;

        $IsEligibleForPayAfterDeliveryRequestDTO = $this->eligibleForPayAfterDeliveryRequestDTOFactory->create(
            $merchantDebtor->getDebtorId(),
            false,
            false,
            $merchantScoreThresholds,
            $debtorScoreThresholds
        );

        return $this->companiesService->isEligibleForPayAfterDelivery($IsEligibleForPayAfterDeliveryRequestDTO);
    }
}
