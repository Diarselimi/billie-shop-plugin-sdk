<?php

namespace spec\App\Application\UseCase\MerchantStartIntegration;

use App\Application\UseCase\MerchantStartIntegration\MerchantStartIntegrationRequest;
use App\Application\UseCase\MerchantStartIntegration\MerchantStartIntegrationUseCase;
use App\DomainEvent\MerchantOnboarding\MerchantIntegrationStarted;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainer;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainerFactory;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionRepositoryInterface;
use App\DomainModel\Sandbox\SandboxClientInterface;
use App\DomainModel\Sandbox\SandboxMerchantDTO;
use App\Helper\Payment\IbanGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MerchantStartIntegrationUseCaseSpec extends ObjectBehavior
{
    public function let(
        MerchantOnboardingContainerFactory $onboardingContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        SandboxClientInterface $sandboxClient,
        CompaniesServiceInterface $companiesService,
        IbanGenerator $ibanGenerator,
        EventDispatcherInterface $eventDispatcher,
        MerchantStartIntegrationRequest $request
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MerchantStartIntegrationUseCase::class);
    }

    public function it_should_dispatch_merchant_sandbox_created_message(
        EventDispatcherInterface $eventDispatcher,
        MerchantRepositoryInterface $merchantRepository,
        MerchantOnboardingContainerFactory $onboardingContainerFactory,
        IbanGenerator $ibanGenerator,
        SandboxClientInterface $sandboxClient,
        CompaniesServiceInterface $companiesService,
        MerchantOnboardingRepositoryInterface $merchantOnboardingRepository,
        MerchantOnboardingTransitionRepositoryInterface $merchantOnboardingTransitionRepository,
        MerchantOnboardingStepRepositoryInterface $merchantOnboardingStepRepository
    ) {
        $merchantId = 1;
        $companyId = 2;
        $merchant = (new MerchantEntity())
            ->setId($merchantId)
            ->setCompanyId($companyId)
            ->setName('dummyName')
            ->setOauthClientId('dummyClientId');
        $request = new MerchantStartIntegrationRequest($merchantId);
        $merchantRepository
            ->getOneById($merchantId)
            ->shouldBeCalledOnce()
            ->willReturn($merchant);

        $step = (new MerchantOnboardingStepEntity())
            ->setName(MerchantOnboardingStepEntity::STEP_TECHNICAL_INTEGRATION)
            ->setState(MerchantOnboardingStepEntity::STATE_NEW);
        $onboardingContainer = new MerchantOnboardingContainer(
            $merchantId,
            $merchantOnboardingRepository->getWrappedObject(),
            $merchantOnboardingTransitionRepository->getWrappedObject(),
            $merchantOnboardingStepRepository->getWrappedObject()
        );
        $onboardingContainer->setOnboardingSteps($step);
        $onboardingContainerFactory
            ->create($merchantId)
            ->shouldBeCalledOnce()
            ->willReturn($onboardingContainer);

        $company = (new DebtorCompany())
            ->setAddressStreet('dummyStreet')
            ->setAddressPostalCode('12345')
            ->setAddressCity('dumyCity')
            ->setAddressCountry('dummyCountry');
        $companiesService
            ->getDebtor($companyId)
            ->shouldBeCalledOnce()
            ->willReturn($company);

        $ibanGenerator
            ->iban(Argument::cetera())
            ->shouldBeCalledOnce()
            ->willReturn('dummyIban');
        $ibanGenerator
            ->bic()
            ->shouldBeCalledOnce()
            ->willReturn('dummyBic');

        $sandboxClientDTO = new SandboxMerchantDTO($merchant, 'dummySecret');
        $sandboxClient
            ->createMerchant(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($sandboxClientDTO);

        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        // Assert
        $eventDispatcher->dispatch(Argument::type(MerchantIntegrationStarted::class))->shouldBeCalled();

        // Act
        $this->execute($request);
    }
}
