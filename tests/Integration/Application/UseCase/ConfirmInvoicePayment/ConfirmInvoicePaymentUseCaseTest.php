<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\UseCase\ConfirmInvoicePayment;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentRequest;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentUseCase;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\Tests\Integration\IntegrationTestCase;
use Ozean12\Money\Money;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see GetInvoicePaymentsUseCase
 */
class ConfirmInvoicePaymentUseCaseTest extends IntegrationTestCase
{
    private const INVOICE_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

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

        $this->useCase = new ConfirmInvoicePaymentUseCase(
            $this->orderRepository->reveal(),
            $this->invoiceService->reveal(),
            $this->paymentsService->reveal(),
            $this->paymentRequestFactory->reveal()
        );

        $this->useCase->setValidator($this->getContainer()->get(ValidatorInterface::class));
    }

    /**
     * @test
     */
    public function shouldFailWithInvalidInvoiceUuid(): void
    {
        $this->expectException(RequestValidationException::class);

        $this->useCase->execute(
            new ConfirmInvoicePaymentRequest('invalid', self::MERCHANT_ID, new Money(1))
        );
    }

    /**
     * @test
     * @dataProvider shouldFailWithInvalidPaidAmountProvider
     * @param Money $invalidMoney
     */
    public function shouldFailWithInvalidPaidAmount(Money $invalidMoney): void
    {
        $this->expectException(RequestValidationException::class);

        $this->useCase->execute(
            new ConfirmInvoicePaymentRequest(self::INVOICE_UUID, self::MERCHANT_ID, $invalidMoney)
        );
    }

    public function shouldFailWithInvalidPaidAmountProvider(): array
    {
        return [
            [
                new Money('not numeric'),
                new Money(''),
                new Money(null),
                new Money(-1.5),
                new Money(0),
            ],
        ];
    }
}
