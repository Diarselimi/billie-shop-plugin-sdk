<?php

namespace App\Application\UseCase\CreateMerchant;

use App\Application\UseCase\CreateMerchant\Exception\CreateMerchantException;
use App\Application\UseCase\CreateMerchant\Exception\DuplicateMerchantCompanyException;
use App\Application\UseCase\CreateMerchant\Exception\MerchantCompanyNotFoundException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Infrastructure\Alfred\AlfredRequestException;
use App\Infrastructure\Alfred\AlfredResponseDecodeException;
use App\Infrastructure\Smaug\AuthenticationServiceException;

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

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantEntityFactory $merchantEntityFactory,
        MerchantSettingsEntityFactory $merchantSettingsFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        ScoreThresholdsConfigurationEntityFactory $scoreThresholdsConfigurationFactory,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository,
        AuthenticationServiceInterface $authenticationService
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
        } catch (AlfredRequestException | AlfredResponseDecodeException $exception) {
            $company = null;
        }

        if (!$company) {
            throw new MerchantCompanyNotFoundException();
        }

        try {
            $oauthClient = $this->authenticationService->createClient($company->getName());
        } catch (AuthenticationServiceException $exception) {
            throw new CreateMerchantException('Failed to create OAuth client for merchant');
        }

        $merchant = $this->merchantFactory->createFromRequest($request, $company);
        $merchant->setOauthClientId($oauthClient->getClientId());
        $this->merchantRepository->insert($merchant);

        $scoreThresholds = $this->scoreThresholdsConfigurationFactory->createDefault();
        $this->scoreThresholdsConfigurationRepository->insert($scoreThresholds);

        $merchantSettings = $this->merchantSettingsFactory->create(
            $merchant->getId(),
            $request->getDebtorFinancingLimit(),
            $scoreThresholds->getId(),
            false,
            MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE
        );
        $this->merchantSettingsRepository->insert($merchantSettings);

        $this->merchantRiskCheckSettingsRepository->insertMerchantDefaultRiskCheckSettings($merchant->getId());

        return new CreateMerchantResponse($merchant, $oauthClient->getClientId(), $oauthClient->getClientSecret());
    }
}
