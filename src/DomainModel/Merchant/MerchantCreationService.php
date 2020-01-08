<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceRequestException;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingPersistenceService;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserRoleEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Helper\Uuid\UuidGeneratorInterface;

class MerchantCreationService
{
    private $merchantRepository;

    private $merchantFactory;

    private $merchantSettingsFactory;

    private $merchantSettingsRepository;

    private $scoreThresholdsConfigurationFactory;

    private $scoreThresholdsConfigurationRepository;

    private $merchantRiskCheckSettingsRepository;

    private $authenticationService;

    private $notificationSettingsFactory;

    private $notificationSettingsRepository;

    private $rolesRepository;

    private $rolesFactory;

    private $onboardingPersistenceService;

    private $uuidGenerator;

    private $merchantAnnouncer;

    private $debtorLimitService;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        MerchantRepositoryInterface $merchantRepository,
        MerchantEntityFactory $merchantEntityFactory,
        MerchantSettingsEntityFactory $merchantSettingsFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        ScoreThresholdsConfigurationEntityFactory $scoreThresholdsConfigurationFactory,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository,
        AuthenticationServiceInterface $authenticationService,
        MerchantNotificationSettingsFactory $notificationSettingsFactory,
        MerchantNotificationSettingsRepositoryInterface $notificationSettingsRepository,
        MerchantUserRoleEntityFactory $rolesFactory,
        MerchantUserRoleRepositoryInterface $rolesRepository,
        MerchantOnboardingPersistenceService $onboardingPersistenceService,
        MerchantAnnouncer $merchantAnnouncer,
        DebtorLimitServiceInterface $debtorLimitService
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->merchantRepository = $merchantRepository;
        $this->merchantFactory = $merchantEntityFactory;
        $this->merchantSettingsFactory = $merchantSettingsFactory;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->scoreThresholdsConfigurationFactory = $scoreThresholdsConfigurationFactory;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->merchantRiskCheckSettingsRepository = $merchantRiskCheckSettingsRepository;
        $this->authenticationService = $authenticationService;
        $this->notificationSettingsFactory = $notificationSettingsFactory;
        $this->notificationSettingsRepository = $notificationSettingsRepository;
        $this->rolesRepository = $rolesRepository;
        $this->rolesFactory = $rolesFactory;
        $this->onboardingPersistenceService = $onboardingPersistenceService;
        $this->merchantAnnouncer = $merchantAnnouncer;
        $this->debtorLimitService = $debtorLimitService;
    }

    public function create(MerchantCreationDTO $creationDTO): MerchantCreationDTO
    {
        $oauthClient = $this->createOauthClient('Merchant: ' . $creationDTO->getCompany()->getName());
        $creationDTO->setOauthClient($oauthClient);

        $merchant = $this->merchantFactory->createFromCreationDTO($creationDTO);
        $this->merchantRepository->insert($merchant);
        $creationDTO->setMerchant($merchant);

        $this->createMerchantDefaults($creationDTO);

        $this->merchantAnnouncer->announceCustomerCreated(
            $creationDTO->getCompany()->getUuid(),
            $creationDTO->getCompany()->getName(),
            $creationDTO->getPaymentUuid()
        );

        return $creationDTO;
    }

    private function createMerchantDefaults(MerchantCreationDTO $creationDTO): void
    {
        $merchantId = $creationDTO->getMerchant()->getId();
        $scoringThresholdId = $this->createDefaultScoringThresholds()->getId();
        $this->createDefaultSettings(
            $merchantId,
            $scoringThresholdId,
            $creationDTO->getInitialDebtorFinancingLimit()
        );
        $this->createDefaultRiskChecks($merchantId);
        $this->createDefaultNotificationSettings($merchantId);
        $this->createDefaultRoles($merchantId);
        $this->createOnboarding($merchantId, $creationDTO->isOnboardComplete());
        $this->createDefaultDebtorLimit($creationDTO);
    }

    private function createOauthClient(string $companyName): AuthenticationServiceCreateClientResponseDTO
    {
        try {
            return $this->authenticationService->createClient($companyName);
        } catch (AuthenticationServiceRequestException $exception) {
            throw new CreateMerchantException('Failed to create OAuth client for merchant');
        }
    }

    private function createDefaultScoringThresholds(): ScoreThresholdsConfigurationEntity
    {
        $scoreThresholds = $this->scoreThresholdsConfigurationFactory->createDefault();
        $this->scoreThresholdsConfigurationRepository->insert($scoreThresholds);

        return $scoreThresholds;
    }

    private function createDefaultSettings(int $merchantId, int $scoreThresholdsId, float $initialDebtorLimit): void
    {
        $merchantSettings = $this->merchantSettingsFactory->create(
            $merchantId,
            $initialDebtorLimit,
            $initialDebtorLimit,
            $scoreThresholdsId,
            false,
            MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE,
            MerchantSettingsEntity::DEFAULT_DEBTOR_FORGIVENESS_THRESHOLD
        );
        $this->merchantSettingsRepository->insert($merchantSettings);
    }

    private function createDefaultRiskChecks(int $merchantId): void
    {
        $this->merchantRiskCheckSettingsRepository->insertMerchantDefaultRiskCheckSettings($merchantId);
    }

    private function createDefaultNotificationSettings(int $merchantId): void
    {
        foreach ($this->notificationSettingsFactory->createDefaults($merchantId) as $notificationSetting) {
            $this->notificationSettingsRepository->insert($notificationSetting);
        }
    }

    private function createDefaultRoles(int $merchantId): void
    {
        foreach (MerchantUserDefaultRoles::ROLES as $role) {
            $this->rolesRepository->create($this->rolesFactory->create(
                $merchantId,
                $this->uuidGenerator->uuid4(),
                $role['name'],
                $role['permissions']
            ));
        }
    }

    private function createOnboarding(int $merchantId, bool $isComplete): void
    {
        if ($isComplete) {
            $this->onboardingPersistenceService->createOnboarded($merchantId);

            return;
        }
        $this->onboardingPersistenceService->createWithSteps($merchantId);
    }

    private function createDefaultDebtorLimit(MerchantCreationDTO $creationDTO): void
    {
        try {
            $this->debtorLimitService->createDefaultDebtorCustomerLimit(
                $creationDTO->getCompany()->getUuid(),
                $creationDTO->getInitialDebtorFinancingLimit()
            );
        } catch (DebtorLimitServiceRequestException $e) {
            throw new CreateMerchantException(
                "Failed to create default debtor-customer limit in Limits service with error {$e->getMessage()}"
            );
        }
    }
}
