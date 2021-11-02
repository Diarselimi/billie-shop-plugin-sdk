<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\ConfirmInvoicePayment;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentNotAllowedException;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentRequest;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentUseCase;
use App\Application\UseCase\ConfirmInvoicePayment\AmountExceededException;
use App\Application\UseCase\GetInvoicePayments\GetInvoicePaymentsUseCase;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see GetInvoicePaymentsUseCase
 */
class ConfirmInvoicePaymentUseCaseTest extends UnitTestCase
{
    private const INVOICE_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

    private const TICKET_UUID = '61feb55b-243d-4df1-9966-aff26605bcaf';

    private const MERCHANT_ID = 123;

    /**
     * @var OrderRepository|ObjectProphecy
     */
    private ObjectProphecy $orderRepository;

    /**
     * @var InvoiceServiceInterface|ObjectProphecy
     */
    private ObjectProphecy $invoiceService;

    /**
     * @var PaymentsServiceInterface|ObjectProphecy
     */
    private ObjectProphecy $paymentsService;

    /**
     * @var PaymentRequestFactory|ObjectProphecy
     */
    private ObjectProphecy $paymentRequestFactory;

    private ConfirmInvoicePaymentUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->prophesize(OrderRepository::class);
        $this->invoiceService = $this->prophesize(InvoiceServiceInterface::class);
        $this->paymentsService = $this->prophesize(PaymentsServiceInterface::class);
        $this->paymentRequestFactory = $this->prophesize(PaymentRequestFactory::class);
        $validator = $this->prophesize(ValidatorInterface::class);
        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList([]));

        $this->useCase = new ConfirmInvoicePaymentUseCase(
            $this->orderRepository->reveal(),
            $this->invoiceService->reveal(),
            $this->paymentsService->reveal(),
            $this->paymentRequestFactory->reveal()
        );

        $this->useCase->setValidator($validator->reveal());
    }

    /**
     * @test
     */
    public function shouldFailIfAssociatedOrderNotFound(): void
    {
        $this->orderRepository->getByInvoiceAndMerchant(
            self::INVOICE_UUID,
            self::MERCHANT_ID
        )->shouldBeCalledOnce()->willReturn(null);

        $this->expectException(InvoiceNotFoundException::class);

        $this->useCase->execute(
            new ConfirmInvoicePaymentRequest(self::INVOICE_UUID, self::MERCHANT_ID, new Money(1))
        );
    }

    /**
     * @test
     */
    public function shouldFailIfInvoiceNotFoundInButlerService(): void
    {
        $this->orderRepository->getByInvoiceAndMerchant(
            self::INVOICE_UUID,
            self::MERCHANT_ID
        )->shouldBeCalledOnce()->willReturn(new OrderEntity());

        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)->shouldBeCalledOnce()->willReturn(null);

        $this->expectException(InvoiceNotFoundException::class);

        $this->useCase->execute(
            new ConfirmInvoicePaymentRequest(self::INVOICE_UUID, self::MERCHANT_ID, new Money(1))
        );
    }

    /**
     * @test
     * @dataProvider shouldFailIfOrderHasIncompatibleStateDataProvider
     * @param string $state
     */
    public function shouldFailIfOrderHasIncompatibleState(string $state): void
    {
        $this->orderRepository->getByInvoiceAndMerchant(
            self::INVOICE_UUID,
            self::MERCHANT_ID
        )->shouldBeCalledOnce()->willReturn(new OrderEntity());

        $invoice = (new Invoice())->setPaymentUuid(self::TICKET_UUID)->setState($state);
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)->shouldBeCalledOnce()->willReturn($invoice);

        $this->expectException(ConfirmInvoicePaymentNotAllowedException::class);

        $this->useCase->execute(
            new ConfirmInvoicePaymentRequest(self::INVOICE_UUID, self::MERCHANT_ID, new Money(1))
        );
    }

    public function shouldFailIfOrderHasIncompatibleStateDataProvider(): array
    {
        return [
            [Invoice::STATE_CANCELED],
            [Invoice::STATE_COMPLETE],
        ];
    }

    /**
     * @test
     */
    public function shouldFailIfAmountExceedsOpenAmount(): void
    {
        $this->orderRepository->getByInvoiceAndMerchant(
            self::INVOICE_UUID,
            self::MERCHANT_ID
        )->shouldBeCalledOnce()->willReturn(new OrderEntity());

        $invoice = (new Invoice())
            ->setPaymentUuid(self::TICKET_UUID)
            ->setState(Invoice::STATE_NEW)
            ->setOutstandingAmount(new Money(10));
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)->shouldBeCalledOnce()->willReturn($invoice);

        $this->expectException(AmountExceededException::class);

        $this->useCase->execute(
            new ConfirmInvoicePaymentRequest(self::INVOICE_UUID, self::MERCHANT_ID, new Money(200))
        );
    }

    /**
     * @test
     */
    public function shouldSucceed(): void
    {
        $order = (new OrderEntity())->setExternalCode('FOOBAR');

        $this->orderRepository->getByInvoiceAndMerchant(
            self::INVOICE_UUID,
            self::MERCHANT_ID
        )->shouldBeCalledOnce()->willReturn($order);

        $invoice = (new Invoice())
            ->setPaymentUuid(self::TICKET_UUID)
            ->setState(Invoice::STATE_NEW)
            ->setOutstandingAmount(new Money(10));
        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)->shouldBeCalledOnce()->willReturn($invoice);

        $paidAmount = new Money(5.35);
        $confirmRequest = new ConfirmRequestDTO($paidAmount->getMoneyValue());

        $this->paymentRequestFactory->createConfirmRequestDTOFromInvoice(
            Argument::is($invoice),
            Argument::is($paidAmount),
            Argument::is($order->getExternalCode())
        )->shouldBeCalledOnce()->willReturn($confirmRequest);

        $this->useCase->execute(
            new ConfirmInvoicePaymentRequest(self::INVOICE_UUID, self::MERCHANT_ID, $paidAmount)
        );
    }
}
