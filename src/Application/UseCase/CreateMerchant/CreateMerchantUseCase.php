<?php

namespace App\Application\UseCase\CreateMerchant;

use App\Application\UseCase\CreateMerchant\Exception\CreateMerchantException;
use App\Application\UseCase\CreateMerchant\Exception\DuplicateMerchantCompanyException;
use App\Application\UseCase\CreateMerchant\Exception\MerchantCompanyNotFoundException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;

class CreateMerchantUseCase
{
    private $merchantRepository;

    private $companiesService;

    private $merchantFactory;

    private $merchantSettingsFactory;

    private $merchantSettingsRepository;

    private $scoreThresholdsConfigurationFactory;

    private $scoreThresholdsConfigurationRepository;

    private $merchantRiskCheckSettingsRepository;

    private $authenticationService;

    private $notificationSettingsFactory;

    private $notificationSettingsRepository;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantEntityFactory $merchantEntityFactory,
        MerchantSettingsEntityFactory $merchantSettingsFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        ScoreThresholdsConfigurationEntityFactory $scoreThresholdsConfigurationFactory,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository,
        AuthenticationServiceInterface $authenticationService,
        MerchantNotificationSettingsFactory $notificationSettingsFactory,
        MerchantNotificationSettingsRepositoryInterface $notificationSettingsRepository
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->companiesService = $companiesService;
        $this->merchantFactory = $merchantEntityFactory;
        $this->merchantSettingsFactory = $merchantSettingsFactory;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->scoreThresholdsConfigurationFactory = $scoreThresholdsConfigurationFactory;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->merchantRiskCheckSettingsRepository = $merchantRiskCheckSettingsRepository;
        $this->authenticationService = $authenticationService;
        $this->notificationSettingsFactory = $notificationSettingsFactory;
        $this->notificationSettingsRepository = $notificationSettingsRepository;
    }

    public function execute(CreateMerchantRequest $request): CreateMerchantResponse
    {
        $companyId = $request->getCompanyId();
        $merchant = $this->merchantRepository->getOneByCompanyId($companyId);

        if ($merchant) {
            throw new DuplicateMerchantCompanyException();
        }

        try {
            $company = $this->companiesService->getDebtor($companyId);
        } catch (CompaniesServiceRequestException $exception) {
            $company = null;
        }

        if (!$company) {
            throw new MerchantCompanyNotFoundException();
        }

        $oauthClient = $this->createOauthClient($company->getName());

        $merchant = $this->merchantFactory->createFromRequest($request, $company);
        $merchant->setOauthClientId($oauthClient->getClientId());
        $this->merchantRepository->insert($merchant);

        $this->createSettings($request, $merchant);

        return new CreateMerchantResponse($merchant, $oauthClient->getClientId(), $oauthClient->getClientSecret());
    }

    private function createOauthClient(string $companyName): AuthenticationServiceCreateClientResponseDTO
    {
        try {
            return $this->authenticationService->createClient($companyName);
        } catch (AuthenticationServiceRequestException $exception) {
            throw new CreateMerchantException('Failed to create OAuth client for merchant');
        }
    }

    private function createSettings(CreateMerchantRequest $request, MerchantEntity $merchant): void
    {
        $scoreThresholds = $this->scoreThresholdsConfigurationFactory->createDefault();

        $this->scoreThresholdsConfigurationRepository->insert($scoreThresholds);

        $merchantSettings = $this->merchantSettingsFactory->create(
            $merchant->getId(),
            $request->getInitialDebtorFinancingLimit(),
            $request->getDebtorFinancingLimit(),
            $scoreThresholds->getId(),
            false,
            MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE,
            MerchantSettingsEntity::DEFAULT_DEBTOR_FORGIVENESS_THRESHOLD
        );

        $this->merchantSettingsRepository->insert($merchantSettings);

        $this->merchantRiskCheckSettingsRepository->insertMerchantDefaultRiskCheckSettings($merchant->getId());

        foreach ($this->notificationSettingsFactory->createDefaults($merchant->getId()) as $notificationSetting) {
            $this->notificationSettingsRepository->insert($notificationSetting);
        }
    }
}
