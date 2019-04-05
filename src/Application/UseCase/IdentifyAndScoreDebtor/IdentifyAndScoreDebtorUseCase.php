<?php

namespace App\Application\UseCase\IdentifyAndScoreDebtor;

use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTOFactory;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
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

    private $eligibleForPayAfterDeliveryRequestDTOFactory;

    private $companiesService;

    private $merchantDebtorRegistrationService;

    private const SCORING_ALGORITHMS = [
        'v1' => 'identifyDebtor',
        'v2' => 'identifyDebtorV2',
    ];

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        IsEligibleForPayAfterDeliveryRequestDTOFactory $eligibleForPayAfterDeliveryRequestDTOFactory,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRegistrationService $merchantDebtorRegistrationService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->eligibleForPayAfterDeliveryRequestDTOFactory = $eligibleForPayAfterDeliveryRequestDTOFactory;
        $this->companiesService = $companiesService;
        $this->merchantDebtorRegistrationService = $merchantDebtorRegistrationService;
    }

    public function execute(IdentifyAndScoreDebtorRequest $request): IdentifyAndScoreDebtorResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());
        $doScoring = $request->isDoScoring();
        $algorithm = $request->getAlgorithm();

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
            ->setIsExperimental(true)
        ;

        /** @var DebtorCompany|null $identifiedDebtor */
        $identifiedDebtor = $this->companiesService->{self::SCORING_ALGORITHMS[$algorithm]}($identifyRequest);
        if (!$identifiedDebtor) {
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

        return (new IdentifyAndScoreDebtorResponse())
            ->setCompanyId($merchantDebtor->getDebtorId())
            ->setCompanyName($identifiedDebtor->getName())
            ->setIsEligible($isEligible)
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
