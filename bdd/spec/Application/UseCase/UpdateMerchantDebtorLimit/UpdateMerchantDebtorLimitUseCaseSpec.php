<?php

namespace spec\App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\UpdateMerchantDebtorLimit\UpdateMerchantDebtorLimitRequest;
use App\Application\UseCase\UpdateMerchantDebtorLimit\UpdateMerchantDebtorLimitUseCase;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateMerchantDebtorLimitUseCaseSpec extends ObjectBehavior
{
    private const DEBTOR_ID = 5000;

    private const MERCHANT_ID = 100;

    private const MERCHANT_UUID = 'merchant-uuid';

    private const MERCHANT_DEBTOR_ID = 15;

    private const MERCHANT_DEBTOR_PAYMENT_ID = 'uuid123';

    private const MERCHANT_DEBTOR_UUID = 'wawawaaaahwaaahahaharrrgggh';

    public function let(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepository $merchantRepository,
        DebtorLimitServiceInterface $debtorLimitService,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($merchantDebtorRepository, $merchantRepository, $debtorLimitService);
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
        $request->getMerchantDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);
        $request->getLimit()->willReturn(100);

        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $merchantDebtorRepository->getOneByUuid(self::MERCHANT_DEBTOR_UUID)->shouldBeCalledOnce()->willReturn(null);

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_the_request_is_not_valid(
        ValidatorInterface $validator,
        UpdateMerchantDebtorLimitRequest $request,
        ConstraintViolation $violation
    ) {
        $request->getLimit()->willReturn(-500);
        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));

        $this->shouldThrow(RequestValidationException::class)->during('execute', [$request]);
    }

    public function it_sets_the_new_limit(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepository $merchantRepository,
        DebtorLimitServiceInterface $debtorLimitService,
        ValidatorInterface $validator,
        UpdateMerchantDebtorLimitRequest $request,
        MerchantDebtorEntity $merchantDebtor,
        MerchantEntity $merchant
    ) {
        $request->getMerchantDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);
        $request->getLimit()->willReturn(1000);

        $this->mockMerchantDebtor($merchantDebtor);
        $this->mockMerchant($merchant);

        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());

        $merchantDebtorRepository
            ->getOneByUuid(self::MERCHANT_DEBTOR_UUID)
            ->shouldBeCalledOnce()
            ->willReturn($merchantDebtor)
        ;

        $merchantRepository
            ->getOneById(self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($merchant)
        ;

        $debtorLimitService
            ->update(self::MERCHANT_DEBTOR_UUID, self::MERCHANT_UUID, 1000)
            ->shouldBeCalled()
        ;

        $this->execute($request);
    }

    private function mockMerchantDebtor(MerchantDebtorEntity $merchantDebtor): void
    {
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);
        $merchantDebtor->getPaymentDebtorId()->willReturn(self::MERCHANT_DEBTOR_PAYMENT_ID);
        $merchantDebtor->getDebtorId()->willReturn(self::DEBTOR_ID);
        $merchantDebtor->getMerchantId()->willReturn(self::MERCHANT_ID);
        $merchantDebtor->getCompanyUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);
    }

    private function mockMerchant(MerchantEntity $merchant)
    {
        $merchant->getId()->willReturn(self::MERCHANT_ID);
        $merchant->getCompanyUuid()->willReturn(self::MERCHANT_UUID);
    }
}
