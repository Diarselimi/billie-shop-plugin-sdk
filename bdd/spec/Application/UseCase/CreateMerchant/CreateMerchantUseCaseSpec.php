<?php

namespace spec\App\Application\UseCase\CreateMerchant;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantResponse;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use App\DomainModel\Merchant\MerchantCreationDTO;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantCreationService;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;
use App\Helper\Uuid\UuidGeneratorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateMerchantUseCaseSpec extends ObjectBehavior
{
    const MERCHANT_ID = 17;

    const MERCHANT_FINANCING_LIMIT = 1000.00;

    const INITIAL_DEBTOR_FINANCING_LIMIT = 200.00;

    const COMPANY_ID = '561';

    public function let(
        UuidGeneratorInterface $uuidGenerator,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantCreationService $merchantCreationService,
        CreateMerchantRequest $request
    ) {
        $request->getCompanyId()->willReturn(self::COMPANY_ID);
        $request->getMerchantFinancingLimit()->willReturn(self::MERCHANT_FINANCING_LIMIT);
        $request->getInitialDebtorFinancingLimit()->willReturn(self::INITIAL_DEBTOR_FINANCING_LIMIT);
        $request->getWebhookUrl()->willReturn(null);
        $request->getWebhookAuthorization()->willReturn(null);

        $this->beConstructedWith(
            $uuidGenerator,
            $merchantRepository,
            $companiesService,
            $merchantCreationService
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateMerchantUseCase::class);
    }

    public function it_throws_exception_if_merchant_exists(
        MerchantRepositoryInterface $merchantRepository,
        CreateMerchantRequest $request,
        MerchantCreationService $merchantCreationService
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(new MerchantEntity());
        $merchantCreationService->create(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(DuplicateMerchantCompanyException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_company_was_not_found(
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        CreateMerchantRequest $request,
        MerchantCreationService $merchantCreationService
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);
        $companiesService->getDebtor(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);
        $merchantCreationService->create(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(MerchantCompanyNotFoundException::class)->during('execute', [$request]);
    }

    public function it_creates_a_new_merchant(
        UuidGeneratorInterface $uuidGenerator,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantCreationService $merchantCreationService,
        CreateMerchantRequest $request
    ) {
        $merchantRepository->getOneByCompanyId(self::COMPANY_ID)->shouldBeCalled()->willReturn(null);

        $companiesService->getDebtor(self::COMPANY_ID)->shouldBeCalled()->willReturn(new DebtorCompany());
        $uuidGenerator->uuid4()->shouldBeCalled()->willReturn('9dec0d0c-06c7-4e0f-86ea-856ca77bd07c');
        $request->getInitialDebtorFinancingLimit()->shouldBeCalled()->willReturn(self::INITIAL_DEBTOR_FINANCING_LIMIT);

        $creationDTO = (new MerchantCreationDTO(new DebtorCompany(), 'api-key', 'payment-uuid', 0, 0));
        $creationDTO->setIsOnboardingComplete(false);
        $creationDTO->setMerchant((new MerchantEntity())->setId(1));
        $creationDTO->setOauthClient(new AuthenticationServiceCreateClientResponseDTO('test-id', 'test-secret'));

        $merchantCreationService->create(Argument::type(MerchantCreationDTO::class))->shouldBeCalled()->willReturn($creationDTO);

        $response = $this->execute($request);
        $response->shouldBeAnInstanceOf(CreateMerchantResponse::class);
    }
}
