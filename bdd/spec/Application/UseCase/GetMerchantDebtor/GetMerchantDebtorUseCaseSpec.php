<?php

namespace spec\App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainer;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
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

    private const MERCHANT_DEBTOR_UUID = 'wawawaaaahwaaahahaharrrgggh';

    public function let(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($merchantDebtorRepository, $merchantDebtorContainerFactory);

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
        $request->getMerchantDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);

        $merchantDebtorRepository->getOneByUuidAndMerchantId(self::MERCHANT_DEBTOR_UUID, self::MERCHANT_ID)
            ->shouldBeCalledOnce()->willReturn(null);

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_the_merchant_debtor_was_not_found_by_uuid(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorRequest $request
    ) {
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);

        $merchantDebtorRepository->getOneByUuidAndMerchantId(self::MERCHANT_DEBTOR_UUID, self::MERCHANT_ID)
            ->shouldBeCalledOnce()->willReturn(null);

        $this->shouldThrow(MerchantDebtorNotFoundException::class)->during('execute', [$request]);
    }

    public function it_returns_the_merchant_debtor_when_found_by_uuid(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        GetMerchantDebtorRequest $request,
        MerchantDebtorEntity $merchantDebtor,
        MerchantDebtorContainer $merchantDebtorContainer
    ) {
        $this->mockMerchantDebtor($merchantDebtor);

        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getMerchantDebtorUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);

        $merchantDebtorRepository->getOneByUuidAndMerchantId(self::MERCHANT_DEBTOR_UUID, self::MERCHANT_ID)
            ->shouldBeCalledOnce()->willReturn($merchantDebtor);

        $merchantDebtorContainerFactory
            ->create($merchantDebtor)
            ->shouldBeCalled()
            ->willReturn($merchantDebtorContainer)
        ;

        $this->execute($request)->shouldBe($merchantDebtorContainer);
    }

    private function mockMerchantDebtor(MerchantDebtorEntity $merchantDebtor): void
    {
        $merchantDebtor->getCompanyUuid()->willReturn(self::MERCHANT_DEBTOR_UUID);
        $merchantDebtor->getId()->willReturn(self::MERCHANT_DEBTOR_ID);
        $merchantDebtor->getDebtorId()->willReturn(self::DEBTOR_ID);
        $merchantDebtor->getPaymentDebtorId()->willReturn(self::MERCHANT_DEBTOR_PAYMENT_ID);
    }
}
