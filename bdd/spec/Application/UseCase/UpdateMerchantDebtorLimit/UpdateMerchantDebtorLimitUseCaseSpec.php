<?php

namespace spec\App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\UpdateMerchantDebtorLimit\UpdateMerchantDebtorLimitRequest;
use App\Application\UseCase\UpdateMerchantDebtorLimit\UpdateMerchantDebtorLimitUseCase;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateMerchantDebtorLimitUseCaseSpec extends ObjectBehavior
{
    private const DEBTOR_ID = 5000;

    private const MERCHANT_ID = 100;

    private const MERCHANT_DEBTOR_ID = 15;

    private const MERCHANT_DEBTOR_PAYMENT_ID = 'uuid123';

    private const MERCHANT_DEBTOR_EXTERNAL_ID = 'TE56DD';

    public function let(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentsService,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($merchantDebtorRepository, $paymentsService);
        $this->setLogger($logger);
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateMerchantDebtorLimitUseCase::class);
    }

    public function it_throws_exception_if_the_merchant_debtor_was_not_found(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        ValidatorInterface $validator,
        UpdateMerchantDebtorLimitRequest $request
    ) {
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorExternalId()->willReturn(self::MERCHANT_DEBTOR_EXTERNAL_ID);
        $request->getLimit()->willReturn(100);

        $validator->validate($request)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $merchantDebtorRepository->getOneByMerchantExternalId(self::MERCHANT_DEBTOR_EXTERNAL_ID, self::MERCHANT_ID, [])->shouldBeCalledOnce()->willReturn(null);

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_the_request_is_not_valid(
        ValidatorInterface $validator,
        UpdateMerchantDebtorLimitRequest $request,
        ConstraintViolation $violation
    ) {
        $request->getLimit()->willReturn(-500);
        $validator->validate($request)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));

        $this->shouldThrow(RequestValidationException::class)->during('execute', [$request]);
    }

    public function it_sets_the_new_limit(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        ValidatorInterface $validator,
        UpdateMerchantDebtorLimitRequest $request,
        BorschtInterface $paymentsService,
        MerchantDebtorEntity $merchantDebtor,
        DebtorPaymentDetailsDTO $debtorPaymentDetails
    ) {
        $this->mockMerchantDebtor($merchantDebtor);
        $this->mockDebtorPaymentDetails($debtorPaymentDetails);

        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorExternalId()->willReturn(self::MERCHANT_DEBTOR_EXTERNAL_ID);
        $request->getLimit()->willReturn(1000);

        $validator->validate($request)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());

        $merchantDebtorRepository->getOneByMerchantExternalId(self::MERCHANT_DEBTOR_EXTERNAL_ID, self::MERCHANT_ID, [])->shouldBeCalledOnce()->willReturn($merchantDebtor);
        $merchantDebtorRepository->getMerchantDebtorCreatedOrdersAmount(self::MERCHANT_DEBTOR_ID)->shouldBeCalledOnce()->willReturn(150.55);

        $paymentsService->getDebtorPaymentDetails(self::MERCHANT_DEBTOR_PAYMENT_ID)->shouldBeCalledOnce()->willReturn($debtorPaymentDetails);

        $merchantDebtor->setFinancingLimit(249.45)->shouldBeCalledOnce();
        $merchantDebtorRepository->update($merchantDebtor)->shouldBeCalledOnce();

        $this->execute($request);
    }

    private function mockMerchantDebtor(MerchantDebtorEntity $merchantDebtor): void
    {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);
        $merchantDebtor->getPaymentDebtorId()->willReturn(self::MERCHANT_DEBTOR_PAYMENT_ID);
        $merchantDebtor->getDebtorId()->willReturn(self::DEBTOR_ID);
        $merchantDebtor->getFinancingLimit()->willReturn(5000);
    }

    private function mockDebtorPaymentDetails(DebtorPaymentDetailsDTO $debtorPaymentDetails): void
    {
        $debtorPaymentDetails->getOutstandingAmount()->willReturn(600);
    }
}
