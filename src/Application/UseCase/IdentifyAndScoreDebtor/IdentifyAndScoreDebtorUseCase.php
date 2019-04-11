<?php

namespace App\Application\UseCase\IdentifyAndScoreDebtor;

use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTOFactory;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRegistrationService;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\OrderRiskCheck\CompanyNameComparator;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;

class IdentifyAndScoreDebtorUseCase
{
    private $merchantRepository;

    private $merchantSettingsRepository;

    private $merchantDebtorRepository;

    private $scoreThresholdsConfigurationRepository;

    private $eligibleForPayAfterDeliveryRequestDTOFactory;

    private $companiesService;

    private $merchantDebtorRegistrationService;

    private $nameComparator;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        IsEligibleForPayAfterDeliveryRequestDTOFactory $eligibleForPayAfterDeliveryRequestDTOFactory,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRegistrationService $merchantDebtorRegistrationService,
        CompanyNameComparator $nameComparator
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->eligibleForPayAfterDeliveryRequestDTOFactory = $eligibleForPayAfterDeliveryRequestDTOFactory;
        $this->companiesService = $companiesService;
        $this->merchantDebtorRegistrationService = $merchantDebtorRegistrationService;
        $this->nameComparator = $nameComparator;
    }

    public function execute(IdentifyAndScoreDebtorRequest $request): IdentifyAndScoreDebtorResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());
        $doScoring = $request->isDoScoring();

        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        $identifyRequest = (new IdentifyDebtorRequestDTO())
            ->setName($request->getName())
            ->setHouseNumber($request->getAddressHouse())
            ->setStreet($request->getAddressStreet())
            ->setPostalCode($request->getAddressPostalCode())
            ->setCity($request->getAddressCity())
            ->setCountry($request->getAddressCountry())
            ->setTaxId($request->getTaxId())
            ->setTaxNumber($request->getTaxNumber())
            ->setRegistrationNumber($request->getRegistrationNumber())
            ->setRegistrationCourt($request->getRegistrationCourt())
            ->setLegalForm($request->getLegalForm())
            ->setFirstName($request->getFirstName())
            ->setLastName($request->getLastName())
            ->setIsExperimental($request->useExperimentalDebtorIdentification())
        ;

        $identifiedDebtor = $this->companiesService->identifyDebtor($identifyRequest);
        if (!$identifiedDebtor || !$identifiedDebtor->isStrictMatch()) {
            throw new DebtorNotIdentifiedException();
        }

        $merchantSettings = $this->merchantSettingsRepository->getOneByMerchant($merchant->getId());

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndDebtorId(
            $merchant->getId(),
            $identifiedDebtor->getId()
        );

        if (!$merchantDebtor) {
            $merchantDebtor = $this->merchantDebtorRegistrationService->registerMerchantDebtor(
                $identifiedDebtor->getId(),
                $merchant
            );
        }

        $isEligible = null;
        if ($doScoring) {
            $isEligible = $this->isEligible($merchantSettings, $merchantDebtor);
        }

        $isNameAccepted = $this->nameComparator->compareWithCompanyName($request->getName(), $identifiedDebtor->getName());

        return (new IdentifyAndScoreDebtorResponse())
            ->setCompanyId($merchantDebtor->getDebtorId())
            ->setCompanyName($identifiedDebtor->getName())
            ->setIsEligible($isEligible)
            ->setIsNameAccepted($isNameAccepted)
            ->setCrefoId($identifiedDebtor->getCrefoId())
        ;
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
