<?php

namespace spec\App\Application\UseCase\CreateMerchant;

use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantResponse;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Infrastructure\Alfred\AlfredRequestException;
use PhpSpec\ObjectBehavior;

class CreateMerchantUseCaseSpec extends ObjectBehavior
{
    const SCORE_CONFIGURATION_ID = 88;

    const MERCHANT_ID = 17;

    const DEBTOR_FINANCING_LIMIT = 500.34;

    const COMPANY_ID = '561';

    const COMPANY_NAME = 'HolaAmigo Company';

    public function let(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantEntityFactory $merchantEntityFactory,
        MerchantSettingsEntityFactory $merchantSettingsFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        ScoreThresholdsConfigurationEntityFactory $scoreThresholdsConfigurationFactory,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        CreateMerchantRequest $request
    ) {
        $request->getCompanyId()->willReturn(self::COMPANY_ID);

        $this->beConstructedWith(
            $merchantRepository,
            $companiesService,
            $merchantEntityFactory,
            $merchantSettingsFactory,
            $merchantSettingsRepository,
            $scoreThresholdsConfigurationFactory,
            $scoreThresholdsConfigurationRepository
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateMerchantUseCase::class);
    }

    public function it_throws_exception_if_merchant_exists(
        MerchantRepositoryInterface $merchantRepository,
        CreateMerchantRequest $request
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(new MerchantEntity());

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_company_was_not_found(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        CreateMerchantRequest $request
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);
        $companiesService->getDebtor(self::COMPANY_ID)->shouldBeCalled()->willThrow(AlfredRequestException::class);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_creates_a_new_merchant(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantEntityFactory $merchantEntityFactory,
        MerchantSettingsEntityFactory $merchantSettingsFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        ScoreThresholdsConfigurationEntityFactory $scoreThresholdsConfigurationFactory,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        CreateMerchantRequest $request,
        DebtorCompany $company,
        MerchantEntity $merchant,
        ScoreThresholdsConfigurationEntity $scoreThresholdsConfiguration,
        MerchantSettingsEntity $merchantSettings
    ) {
        $company->getName()->willReturn(self::COMPANY_NAME);
        $merchant->getId()->willReturn(self::MERCHANT_ID);
        $scoreThresholdsConfiguration->getId()->willReturn(self::SCORE_CONFIGURATION_ID);

        $request->getDebtorFinancingLimit()->willReturn(self::DEBTOR_FINANCING_LIMIT);

        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);
        $merchantRepository->insert($merchant)->shouldBeCalled();
        $companiesService->getDebtor(self::COMPANY_ID)->shouldBeCalled()->willReturn($company);
        $merchantEntityFactory->createFromRequest($request, $company)->shouldBeCalledOnce()->willReturn($merchant);

        $scoreThresholdsConfigurationFactory->createDefault()->shouldBeCalledOnce()->willReturn($scoreThresholdsConfiguration);
        $scoreThresholdsConfigurationRepository->insert($scoreThresholdsConfiguration)->shouldBeCalledOnce();

        $merchantSettingsFactory->create(self::MERCHANT_ID, self::DEBTOR_FINANCING_LIMIT, self::SCORE_CONFIGURATION_ID, false)->willReturn($merchantSettings);
        $merchantSettingsRepository->insert($merchantSettings)->shouldBeCalledOnce();

        $response = $this->execute($request);
        $response->shouldBeAnInstanceOf(CreateMerchantResponse::class);
    }
}
