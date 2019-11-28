<?php

namespace spec\App\Application\UseCase\CreateMerchant;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantResponse;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use App\Application\UseCase\CreateMerchant\Exception\DuplicateMerchantCompanyException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantAnnouncer;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsEntity;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainer;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingPersistenceService;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateMerchantUseCaseSpec extends ObjectBehavior
{
    const SCORE_CONFIGURATION_ID = 88;

    const MERCHANT_ID = 17;

    const INITIAL_DEBTOR_FINANCING_LIMIT = 200.00;

    const DEBTOR_FINANCING_LIMIT = 500.34;

    const COMPANY_ID = '561';

    const COMPANY_NAME = 'HolaAmigo Company';

    const COMPANY_UUID = 'c7be46c0-e049-4312-b274-258ec5aeeb70';

    const PAYMENT_UUID = 'c7be46c0-e049-4312-b274-258ec5aeeb70';

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
        MerchantNotificationSettingsFactory $notificationSettingsFactory,
        MerchantNotificationSettingsRepositoryInterface $notificationSettingsRepository,
        CreateMerchantRequest $request,
        MerchantUserRoleRepositoryInterface $rolesRepository,
        MerchantUserRoleEntityFactory $rolesFactory,
        MerchantOnboardingPersistenceService $onboardingPersistenceService,
        MerchantOnboardingContainer $onboardingContainer,
        MerchantAnnouncer $merchantAnnouncer
    ) {
        $request->getCompanyId()->willReturn(self::COMPANY_ID);
        $onboardingPersistenceService->createWithSteps(Argument::any())->willReturn($onboardingContainer);

        $this->beConstructedWith(
            $merchantRepository,
            $companiesService,
            $merchantEntityFactory,
            $merchantSettingsFactory,
            $merchantSettingsRepository,
            $scoreThresholdsConfigurationFactory,
            $scoreThresholdsConfigurationRepository,
            $merchantRiskCheckSettingsRepository,
            $authenticationService,
            $notificationSettingsFactory,
            $notificationSettingsRepository,
            $rolesFactory,
            $rolesRepository,
            $onboardingPersistenceService,
            $merchantAnnouncer
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateMerchantUseCase::class);
    }

    public function it_throws_exception_if_merchant_exists(
        MerchantRepositoryInterface $merchantRepository,
        CreateMerchantRequest $request,
        MerchantOnboardingPersistenceService $onboardingPersistenceService
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(new MerchantEntity());
        $onboardingPersistenceService->createWithSteps(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(DuplicateMerchantCompanyException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_company_was_not_found(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        CreateMerchantRequest $request,
        MerchantOnboardingPersistenceService $onboardingPersistenceService
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);
        $companiesService->getDebtor(self::COMPANY_ID)->shouldBeCalled()->willThrow(CompaniesServiceRequestException::class);
        $onboardingPersistenceService->createWithSteps(Argument::any())->shouldNotBeCalled();

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
        MerchantNotificationSettingsFactory $notificationSettingsFactory,
        MerchantNotificationSettingsRepositoryInterface $notificationSettingsRepository,
        AuthenticationServiceInterface $authenticationService,
        CreateMerchantRequest $request,
        DebtorCompany $company,
        MerchantEntity $merchant,
        ScoreThresholdsConfigurationEntity $scoreThresholdsConfiguration,
        MerchantSettingsEntity $merchantSettings,
        MerchantNotificationSettingsEntity $merchantNotificationSettingsEntity,
        MerchantUserRoleRepositoryInterface $rolesRepository,
        MerchantUserRoleEntityFactory $rolesFactory,
        MerchantOnboardingPersistenceService $onboardingPersistenceService,
        MerchantAnnouncer $merchantAnnouncer,
        MessageBusInterface $bus
    ) {
        $company->getName()->willReturn(self::COMPANY_NAME);
        $company->getUuid()->willReturn(self::COMPANY_UUID);
        $merchant->getId()->willReturn(self::MERCHANT_ID);
        $merchant->getPaymentUuid()->willReturn(self::PAYMENT_UUID);
        $scoreThresholdsConfiguration->getId()->willReturn(self::SCORE_CONFIGURATION_ID);

        $request->getInitialDebtorFinancingLimit()->willReturn(self::INITIAL_DEBTOR_FINANCING_LIMIT);
        $request->getDebtorFinancingLimit()->willReturn(self::DEBTOR_FINANCING_LIMIT);
        $onboardingPersistenceService->createWithSteps(Argument::any())->shouldBeCalled();

        $roleExample = new MerchantUserRoleEntity();
        $rolesFactory
            ->create(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn($roleExample);

        $rolesRepository->create($roleExample)->shouldBeCalledTimes(count(MerchantUserDefaultRoles::ROLES));

        $authenticationService
            ->createClient(self::COMPANY_NAME)
            ->shouldBeCalled()
            ->willReturn(new AuthenticationServiceCreateClientResponseDTO('oauthClientId', 'oauthClientSecret'));

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

        $notificationSettingsFactory
            ->createDefaults(self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn([$merchantNotificationSettingsEntity]);
        $notificationSettingsRepository->insert($merchantNotificationSettingsEntity)->shouldBeCalled();

        $merchantAnnouncer->customerCreated(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

        $response = $this->execute($request);
        $response->shouldBeAnInstanceOf(CreateMerchantResponse::class);
    }
}
