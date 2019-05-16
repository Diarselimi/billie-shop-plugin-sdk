<?php

namespace spec\App\Application\UseCase\CreateMerchant;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantResponse;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use App\Application\UseCase\CreateMerchant\Exception\DuplicateMerchantCompanyException;
use App\Application\UseCase\CreateMerchant\Exception\MerchantCompanyNotFoundException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use App\Infrastructure\Alfred\AlfredRequestException;
use PhpSpec\ObjectBehavior;

class CreateMerchantUseCaseSpec extends ObjectBehavior
{
    const SCORE_CONFIGURATION_ID = 88;

    const MERCHANT_ID = 17;

    const INITIAL_DEBTOR_FINANCING_LIMIT = 200.00;

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
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository,
        AuthenticationServiceInterface $authenticationService,
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
            $scoreThresholdsConfigurationRepository,
            $merchantRiskCheckSettingsRepository,
            $authenticationService
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

        $this->shouldThrow(DuplicateMerchantCompanyException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_company_was_not_found(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        CreateMerchantRequest $request
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);
        $companiesService->getDebtor(self::COMPANY_ID)->shouldBeCalled()->willThrow(AlfredRequestException::class);

        $this->shouldThrow(MerchantCompanyNotFoundException::class)->during('execute', [$request]);
    }

    public function it_creates_a_new_merchant(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantEntityFactory $merchantEntityFactory,
        MerchantSettingsEntityFactory $merchantSettingsFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        ScoreThresholdsConfigurationEntityFactory $scoreThresholdsConfigurationFactory,
        ScoreThresholdsConfigurationRepositoryInterface $scoreThresholdsConfigurationRepository,
        MerchantRiskCheckSettingsRepositoryInterface $merchantRiskCheckSettingsRepository,
        CreateMerchantRequest $request,
        DebtorCompany $company,
        MerchantEntity $merchant,
        ScoreThresholdsConfigurationEntity $scoreThresholdsConfiguration,
        MerchantSettingsEntity $merchantSettings,
        AuthenticationServiceInterface $authenticationService
    ) {
        $company->getName()->willReturn(self::COMPANY_NAME);
        $merchant->getId()->willReturn(self::MERCHANT_ID);
        $scoreThresholdsConfiguration->getId()->willReturn(self::SCORE_CONFIGURATION_ID);

        $request->getInitialDebtorFinancingLimit()->willReturn(self::INITIAL_DEBTOR_FINANCING_LIMIT);
        $request->getDebtorFinancingLimit()->willReturn(self::DEBTOR_FINANCING_LIMIT);

        $authenticationService
            ->createClient(self::COMPANY_NAME)
            ->shouldBeCalled()
            ->willReturn(new AuthenticationServiceCreateClientResponseDTO('oauthClientId', 'oauthClientSecret'))
        ;

        $merchant->setOauthClientId('oauthClientId')->shouldBeCalled();

        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);
        $merchantRepository->insert($merchant)->shouldBeCalled();
        $companiesService->getDebtor(self::COMPANY_ID)->shouldBeCalled()->willReturn($company);
        $merchantEntityFactory->createFromRequest($request, $company)->shouldBeCalledOnce()->willReturn($merchant);

        $scoreThresholdsConfigurationFactory->createDefault()->shouldBeCalledOnce()->willReturn($scoreThresholdsConfiguration);
        $scoreThresholdsConfigurationRepository->insert($scoreThresholdsConfiguration)->shouldBeCalledOnce();

        $merchantSettingsFactory
            ->create(
                self::MERCHANT_ID,
                self::INITIAL_DEBTOR_FINANCING_LIMIT,
                self::DEBTOR_FINANCING_LIMIT,
                self::SCORE_CONFIGURATION_ID,
                false,
                'none',
                1.0
            )
            ->willReturn($merchantSettings);
        $merchantSettingsRepository->insert($merchantSettings)->shouldBeCalledOnce();

        $merchantRiskCheckSettingsRepository->insertMerchantDefaultRiskCheckSettings(self::MERCHANT_ID)->shouldBeCalledOnce();

        $response = $this->execute($request);
        $response->shouldBeAnInstanceOf(CreateMerchantResponse::class);
    }
}
