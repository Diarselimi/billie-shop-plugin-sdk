<?php

namespace spec\App\DomainModel\Sandbox;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Sandbox\SandboxClientInterface;
use App\DomainModel\Sandbox\SandboxCreationException;
use App\DomainModel\Sandbox\SandboxMerchantCreationService;
use App\DomainModel\Sandbox\SandboxMerchantDTO;
use App\DomainModel\Sandbox\SandboxServiceRequestException;
use App\Helper\Payment\IbanGenerator;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use EightPoints\Bundle\GuzzleBundle\Log\LoggerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SandboxMerchantCreationServiceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SandboxMerchantCreationService::class);
    }

    public function let(
        MerchantRepositoryInterface $merchantRepository,
        SandboxClientInterface $sandboxClient,
        CompaniesServiceInterface $companiesService,
        IbanGenerator $ibanGenerator,
        LoggerInterface $logger,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
        $this->setSentry($sentry);
    }

    public function it_successfully_calls_create_sandbox_and_updates_merchant(
        CompaniesServiceInterface $companiesService,
        SandboxClientInterface $sandboxClient,
        MerchantRepositoryInterface $merchantRepository,
        IbanGenerator $ibanGenerator,
        DebtorCompany $company,
        MerchantEntity $merchant
    ) {
        $merchant->getCompanyId()->willReturn(1);
        $merchant->getName()->willReturn('random-name');
        $this->mockCompany($company);
        $companiesService
            ->getDebtor(1)
            ->willReturn($company);

        $ibanGenerator->iban('DE', null, 24)->willReturn('random-iban');
        $ibanGenerator->bic()->willReturn('random-bic');

        $sandboxClientDTO = new SandboxMerchantDTO(new MerchantEntity, 'oauthClientSecret');
        $sandboxClient
            ->createMerchant(Argument::cetera())
            ->willReturn($sandboxClientDTO);

        $merchant->setSandboxPaymentUuid(Argument::cetera())->willReturn($merchant);

        $merchantRepository
            ->update($merchant)
            ->shouldBeCalled();

        $this->create($merchant)->shouldBe(null);
    }

    public function it_fails_to_create_sandbox_and_throws_exception(
        CompaniesServiceInterface $companiesService,
        SandboxClientInterface $sandboxClient,
        MerchantRepositoryInterface $merchantRepository,
        IbanGenerator $ibanGenerator,
        DebtorCompany $company,
        MerchantEntity $merchant,
        RavenClient $sentry
    ) {
        $merchant->getCompanyId()->willReturn(1);
        $merchant->getName()->willReturn('random-name');
        $merchant->getId()->willReturn(1);
        $this->mockCompany($company);
        $companiesService
            ->getDebtor(1)
            ->willReturn($company);

        $ibanGenerator->iban('DE', null, 24)->willReturn('random-iban');
        $ibanGenerator->bic()->willReturn('random-bic');

        $sandboxClient
            ->createMerchant(Argument::cetera())
            ->shouldBeCalled()
            ->willThrow(SandboxServiceRequestException::class);

        $sentry->captureException(Argument::any())->shouldBeCalled();

        $merchantRepository
            ->update($merchant)
            ->shouldNotBeCalled();

        $this->shouldThrow(new SandboxCreationException('Sandbox merchant cannot be created at this point.'))
            ->during('create', [$merchant]);
    }

    private function mockCompany(DebtorCompany $company)
    {
        $company->getSchufaId()->willReturn(null);
        $company->getCrefoId()->willReturn(null);
        $company->getAddressHouse()->willReturn(null);
        $company->getLegalForm()->willReturn(null);
        $company->getAddressStreet()->willReturn('addressStreet');
        $company->getAddressPostalCode()->willReturn('addressPostalCode');
        $company->getAddressCity()->willReturn('addressCity');
        $company->getAddressCountry()->willReturn('addressCountry');
    }
}
