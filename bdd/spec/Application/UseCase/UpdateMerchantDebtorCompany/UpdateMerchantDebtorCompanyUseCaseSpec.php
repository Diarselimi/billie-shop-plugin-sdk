<?php

namespace spec\App\Application\UseCase\UpdateMerchantDebtorCompany;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\UpdateMerchantDebtorCompany\UpdateMerchantDebtorCompanyRequest;
use App\Application\UseCase\UpdateMerchantDebtorCompany\UpdateMerchantDebtorCompanyUseCase;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateMerchantDebtorCompanyUseCaseSpec extends ObjectBehavior
{
    private const DEBTOR_ID = 5000;

    private const MERCHANT_ID = 100;

    private const MERCHANT_DEBTOR_ID = 15;

    private const MERCHANT_DEBTOR_UUID = 'wawawaaaahwaaahahaharrrgggh';

    public function let(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($merchantDebtorRepository, $companiesService);
        $this->setLogger($logger);
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateMerchantDebtorCompanyUseCase::class);
    }

    public function it_throws_exception_if_the_merchant_debtor_was_not_found(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        ValidatorInterface $validator,
        UpdateMerchantDebtorCompanyRequest $request
    ) {
        $this->mockRequest($request);

        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $merchantDebtorRepository->getOneByUuid(self::MERCHANT_DEBTOR_UUID)->shouldBeCalledOnce()->willReturn(null);

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_fails_when_companies_service_fails(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        UpdateMerchantDebtorCompanyRequest $request,
        CompaniesServiceInterface $companiesService,
        ValidatorInterface $validator,
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $debtorCompany
    ) {
        $this->mockRequest($request);
        $this->mockMerchantDebtor($merchantDebtor);
        $this->mockDebtorCompany($debtorCompany);

        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $merchantDebtorRepository->getOneByUuid(self::MERCHANT_DEBTOR_UUID)->shouldBeCalledOnce()->willReturn($merchantDebtor);
        $companiesService->getDebtor(self::DEBTOR_ID)->shouldBeCalledOnce()->willReturn($debtorCompany);
        $companiesService->updateDebtor(self::DEBTOR_ID, [
            'name' => 'Billie1',
            'address_house' => 'House1',
            'address_street' => 'Street1',
            'address_city' => 'City1',
            'address_postal_code' => 'PostalCode1',
        ])->shouldBeCalledOnce()->willThrow(CompaniesServiceRequestException::class);

        $this->shouldThrow(CompaniesServiceRequestException::class)->during('execute', [$request]);
    }

    public function it_updates_the_company_data(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        UpdateMerchantDebtorCompanyRequest $request,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorEntity $merchantDebtor,
        ValidatorInterface $validator,
        DebtorCompany $debtorCompany
    ) {
        $this->mockRequest($request);
        $this->mockMerchantDebtor($merchantDebtor);
        $this->mockDebtorCompany($debtorCompany);

        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $merchantDebtorRepository->getOneByUuid(self::MERCHANT_DEBTOR_UUID)->shouldBeCalledOnce()->willReturn($merchantDebtor);
        $companiesService->getDebtor(self::DEBTOR_ID)->shouldBeCalledOnce()->willReturn($debtorCompany);
        $companiesService->updateDebtor(self::DEBTOR_ID, [
            'name' => 'Billie1',
            'address_house' => 'House1',
            'address_street' => 'Street1',
            'address_city' => 'City1',
            'address_postal_code' => 'PostalCode1',
        ])->shouldBeCalledOnce()->willReturn($debtorCompany);

        $this->execute($request);
    }

    private function mockMerchantDebtor(MerchantDebtorEntity $merchantDebtor): void
    {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);
        $merchantDebtor->getUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);
        $merchantDebtor->getDebtorId()->willReturn(self::DEBTOR_ID);
        $merchantDebtor->getMerchantId()->willReturn(self::MERCHANT_ID);
    }

    private function mockRequest(UpdateMerchantDebtorCompanyRequest $request): void
    {
        $request->getDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);
        $request->getName()->willReturn('Billie1');
        $request->getAddressHouse()->willReturn('House1');
        $request->getAddressStreet()->willReturn('Street1');
        $request->getAddressPostalCode()->willReturn('PostalCode1');
        $request->getAddressCity()->willReturn('City1');
    }

    private function mockDebtorCompany(DebtorCompany $debtorCompany): void
    {
        $debtorCompany->getId()->willReturn(self::DEBTOR_ID);
        $debtorCompany->getName()->willReturn('Billie');
        $debtorCompany->getAddressHouse()->willReturn('House');
        $debtorCompany->getAddressStreet()->willReturn('Street');
        $debtorCompany->getAddressPostalCode()->willReturn('PostalCode');
        $debtorCompany->getAddressCity()->willReturn('City');
    }
}
