<?php

namespace spec\App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainer;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use App\DomainModel\Order\OrderStateManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetMerchantDebtorUseCaseSpec extends ObjectBehavior
{
    private const DEBTOR_ID = '5000';

    private const MERCHANT_ID = 100;

    private const MERCHANT_DEBTOR_ID = 15;

    private const MERCHANT_DEBTOR_PAYMENT_ID = 'uuid123';

    private const MERCHANT_DEBTOR_EXTERNAL_ID = 'TE56DD';

    private const MERCHANT_DEBTOR_UUID = 'wawawaaaahwaaahahaharrrgggh';

    public function let(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentService,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorFinancialDetailsRepositoryInterface $financialDetailsRepository,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($merchantDebtorRepository, $paymentService, $companiesService, $financialDetailsRepository);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GetMerchantDebtorUseCase::class);
    }

    public function it_throws_exception_if_the_merchant_debtor_was_not_found(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorRequest $request
    ) {
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorExternalId()->willReturn(self::MERCHANT_DEBTOR_EXTERNAL_ID);
        $request->getMerchantDebtorUuid()->willReturn(null);

        $merchantDebtorRepository->getOneByExternalIdAndMerchantId(self::MERCHANT_DEBTOR_EXTERNAL_ID, self::MERCHANT_ID, [])
            ->shouldBeCalledOnce()->willReturn(null);

        $merchantDebtorRepository->getOneByUuidAndMerchantId(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_the_merchant_debtor_was_not_found_by_uuid(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorRequest $request
    ) {
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorExternalId()->willReturn(null);
        $request->getMerchantDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);

        $merchantDebtorRepository->getOneByUuidAndMerchantId(self::MERCHANT_DEBTOR_UUID, self::MERCHANT_ID)
            ->shouldBeCalledOnce()->willReturn(null);

        $merchantDebtorRepository->getOneByExternalIdAndMerchantId(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_returns_the_merchant_debtor_when_found_by_uuid(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorRequest $request,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorFinancialDetailsRepositoryInterface $financialDetailsRepository,
        MerchantDebtorFinancialDetailsEntity $financialDetails,
        CompaniesServiceInterface $companiesService,
        DebtorCompany $debtorCompany,
        BorschtInterface $paymentService,
        DebtorPaymentDetailsDTO $paymentDetails
    ) {
        $this->mockMerchantDebtor($merchantDebtor);

        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorExternalId()->willReturn(null);
        $request->getMerchantDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);

        $merchantDebtorRepository->getOneByUuidAndMerchantId(self::MERCHANT_DEBTOR_UUID, self::MERCHANT_ID)
            ->shouldBeCalledOnce()->willReturn($merchantDebtor);

        $merchantDebtorRepository->getOneByExternalIdAndMerchantId(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $merchantDebtorRepository->findExternalId(self::MERCHANT_DEBTOR_ID)
            ->shouldBeCalledOnce()->willReturn(self::MERCHANT_DEBTOR_EXTERNAL_ID);

        $financialDetailsRepository->getCurrentByMerchantDebtorId(self::MERCHANT_DEBTOR_ID)
            ->shouldBeCalledOnce()->willReturn($financialDetails);

        $companiesService->getDebtor(self::DEBTOR_ID)
            ->shouldBeCalledOnce()->willReturn($debtorCompany);

        $paymentService->getDebtorPaymentDetails(self::MERCHANT_DEBTOR_PAYMENT_ID)
            ->shouldBeCalledOnce()->willReturn($paymentDetails);

        $merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState(self::MERCHANT_DEBTOR_ID, OrderStateManager::STATE_CREATED)
            ->shouldBeCalledOnce()->willReturn(123.456);

        $merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState(self::MERCHANT_DEBTOR_ID, OrderStateManager::STATE_LATE)
            ->shouldBeCalledOnce()->willReturn(7.89);

        $this->execute($request)->shouldBeAnInstanceOf(MerchantDebtorContainer::class);
    }

    private function mockMerchantDebtor(MerchantDebtorEntity $merchantDebtor): void
    {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);
        $merchantDebtor->getDebtorId()->willReturn(self::DEBTOR_ID);
        $merchantDebtor->getPaymentDebtorId()->willReturn(self::MERCHANT_DEBTOR_PAYMENT_ID);
    }
}
