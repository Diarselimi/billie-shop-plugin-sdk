<?php

namespace App\Application\UseCase\IdentifyAndScoreDebtor;

use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorScoring\DebtorScoringRequestDTOFactory;
use App\DomainModel\DebtorScoring\ScoringServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRegistrationService;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;

class IdentifyAndScoreDebtorUseCase
{
    private $merchantRepository;

    private $merchantSettingsRepository;

    private $merchantDebtorRepository;

    private $scoreThresholdsConfigurationRepository;

    private $debtorScoringRequestDTOFactory;

    private $companiesService;

    private $merchantDebtorRegistrationService;

    private $debtorLimitService;

    private $scoringService;

    public function __construct(
        MerchantRepository $merchantRepository,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        DebtorScoringRequestDTOFactory $debtorScoringRequestDTOFactory,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRegistrationService $merchantDebtorRegistrationService,
        DebtorLimitServiceInterface $debtorLimitService,
        ScoringServiceInterface $scoringService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->debtorScoringRequestDTOFactory = $debtorScoringRequestDTOFactory;
        $this->companiesService = $companiesService;
        $this->merchantDebtorRegistrationService = $merchantDebtorRegistrationService;
        $this->debtorLimitService = $debtorLimitService;
        $this->scoringService = $scoringService;
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
            ->setBillingAddress($this->createBillingAddress($request))
        ;

        $identifyDebtorResponseDTO = $this->companiesService->identifyDebtor($identifyRequest);
        $identifiedDebtor = $identifyDebtorResponseDTO->getIdentifiedDebtorCompany();
        if (!$identifiedDebtor || !$identifiedDebtor->isStrictMatch()) {
            throw new DebtorNotIdentifiedException();
        }

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndCompanyUuid(
            $merchant->getId(),
            $identifiedDebtor->getUuid()
        );

        if (!$merchantDebtor) {
            $merchantDebtor = $this->merchantDebtorRegistrationService->registerMerchantDebtor(
                $identifiedDebtor,
                $merchant
            );
        }

        $merchantSettings = $this->merchantSettingsRepository->getOneByMerchant($merchant->getId());

        if ($request->getLimit()) {
            $this->setLimit($merchant, $merchantDebtor, $request->getLimit());
        }

        $isEligible = null;
        if ($doScoring) {
            $isEligible = $this->isEligible($merchantSettings, $merchantDebtor);
        }

        return (new IdentifyAndScoreDebtorResponse())
            ->setCompanyId($merchantDebtor->getDebtorId())
            ->setCompanyName($identifiedDebtor->getName())
            ->setIsEligible($isEligible)
            ->setIsStrictMatch($identifiedDebtor->isStrictMatch())
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

        $debtorScoringRequestDTO = $this->debtorScoringRequestDTOFactory->create(
            $merchantDebtor->getCompanyUuid(),
            false,
            false,
            $merchantScoreThresholds,
            $debtorScoreThresholds
        );

        return $this->scoringService->scoreDebtor($debtorScoringRequestDTO)->isEligible();
    }

    private function setLimit(MerchantEntity $merchant, MerchantDebtorEntity $merchantDebtor, float $limit)
    {
        $this->debtorLimitService->check($merchantDebtor->getCompanyUuid(), $merchant->getCompanyUuid(), 0.1);

        $this->debtorLimitService->update($merchantDebtor->getCompanyUuid(), $merchant->getCompanyUuid(), $limit);
    }

    private function createBillingAddress(IdentifyAndScoreDebtorRequest $request): AddressEntity
    {
        return (new AddressEntity())
            ->setPostalCode($request->getAddressPostalCode())
            ->setStreet($request->getAddressStreet())
            ->setCity($request->getAddressCity())
            ->setCountry($request->getAddressCountry())
            ->setHouseNumber($request->getAddressHouse());
    }
}
