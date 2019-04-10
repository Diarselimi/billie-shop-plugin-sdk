<?php

namespace App\Application\UseCase\CreateMerchant;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Infrastructure\Alfred\AlfredRequestException;
use App\Infrastructure\Alfred\AlfredResponseDecodeException;
use App\Infrastructure\Repository\MerchantRiskCheckSettingsRepository;
use Symfony\Component\HttpFoundation\Response;

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

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantEntityFactory $merchantEntityFactory,
        MerchantSettingsEntityFactory $merchantSettingsFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        ScoreThresholdsConfigurationEntityFactory $scoreThresholdsConfigurationFactory,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->companiesService = $companiesService;
        $this->merchantFactory = $merchantEntityFactory;
        $this->merchantSettingsFactory = $merchantSettingsFactory;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->scoreThresholdsConfigurationFactory = $scoreThresholdsConfigurationFactory;
        $this->scoreThresholdsConfigurationRepository = $scoreThresholdsConfigurationRepository;
        $this->merchantRiskCheckSettingsRepository = $merchantRiskCheckSettingsRepository;
    }

    public function execute(CreateMerchantRequest $request): CreateMerchantResponse
    {
        $companyId = $request->getCompanyId();
        $merchant = $this->merchantRepository->getOneByCompanyId($companyId);

        if ($merchant) {
            throw new PaellaCoreCriticalException(
                "Merchant with company id $companyId already exists",
                PaellaCoreCriticalException::CODE_NOT_FOUND, // TODO: shit
                Response::HTTP_CONFLICT
            );
        }

        try {
            $company = $this->companiesService->getDebtor($companyId);
        } catch (AlfredRequestException | AlfredResponseDecodeException $exception) {
            $company = null;
        }

        if (!$company) {
            throw new PaellaCoreCriticalException(
                "Company id $companyId can't be retrieved",
                PaellaCoreCriticalException::CODE_NOT_FOUND, // TODO: shit
                Response::HTTP_BAD_REQUEST
            );
        }

        $merchant = $this->merchantFactory->createFromRequest($request, $company);
        $this->merchantRepository->insert($merchant);

        $scoreThresholds = $this->scoreThresholdsConfigurationFactory->createDefault();
        $this->scoreThresholdsConfigurationRepository->insert($scoreThresholds);

        $merchantSettings = $this->merchantSettingsFactory->create($merchant->getId(), $request->getDebtorFinancingLimit(), $scoreThresholds->getId());
        $this->merchantSettingsRepository->insert($merchantSettings);

        $this->merchantRiskCheckSettingsRepository->insertMerchantDefaultRiskCheckSettings($merchant->getId());

        return new CreateMerchantResponse($merchant);
    }
}
