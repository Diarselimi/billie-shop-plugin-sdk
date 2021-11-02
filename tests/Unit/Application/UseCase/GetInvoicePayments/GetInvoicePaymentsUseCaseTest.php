<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\GetInvoicePayments;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoicePayments\GetInvoicePaymentsRequest;
use App\Application\UseCase\GetInvoicePayments\GetInvoicePaymentsUseCase;
use App\Application\UseCase\GetInvoicePayments\Response\GetInvoicePaymentsResponse;
use App\Application\UseCase\GetInvoicePayments\Response\GetInvoicePaymentsResponseFactory;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\Support\PaginatedCollection;
use App\Tests\Unit\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @see GetInvoicePaymentsUseCase
 */
class GetInvoicePaymentsUseCaseTest extends UnitTestCase
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
     * @var PaymentsRepositoryInterface|ObjectProphecy
     */
    private ObjectProphecy $paymentsRepository;

    /**
     * @var GetInvoicePaymentsResponseFactory|ObjectProphecy
     */
    private ObjectProphecy $responseFactory;

    private GetInvoicePaymentsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->prophesize(OrderRepository::class);
        $this->invoiceService = $this->prophesize(InvoiceServiceInterface::class);
        $this->paymentsRepository = $this->prophesize(PaymentsRepositoryInterface::class);
        $this->responseFactory = $this->prophesize(GetInvoicePaymentsResponseFactory::class);

        $this->useCase = new GetInvoicePaymentsUseCase(
            $this->orderRepository->reveal(),
            $this->invoiceService->reveal(),
            $this->paymentsRepository->reveal(),
            $this->responseFactory->reveal()
        );
    }

    /**
     * @test
     */
    public function shouldFailIfInvoiceNotFoundInRepository(): void
    {
        $this->orderRepository->getByInvoiceAndMerchant(
            self::INVOICE_UUID,
            self::MERCHANT_ID
        )->shouldBeCalledOnce()->willReturn(null);

        $this->expectException(InvoiceNotFoundException::class);

        $this->useCase->execute(new GetInvoicePaymentsRequest(self::INVOICE_UUID, self::MERCHANT_ID));
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

        $this->useCase->execute(new GetInvoicePaymentsRequest(self::INVOICE_UUID, self::MERCHANT_ID));
    }

    /**
     * @test
     */
    public function shouldSucceed(): void
    {
        $this->orderRepository->getByInvoiceAndMerchant(
            self::INVOICE_UUID,
            self::MERCHANT_ID
        )->shouldBeCalledOnce()->willReturn(new OrderEntity());

        $invoice = (new Invoice())->setPaymentUuid(self::TICKET_UUID);
        $transactions = new PaginatedCollection();

        $this->invoiceService->getOneByUuid(self::INVOICE_UUID)->shouldBeCalledOnce()
            ->willReturn($invoice);

        $this->paymentsRepository->getTicketPayments(self::TICKET_UUID)->shouldBeCalledOnce()
            ->willReturn($transactions);

        $this->responseFactory->create($invoice, $transactions)
            ->shouldBeCalledOnce()
            ->willReturn(new GetInvoicePaymentsResponse());

        $this->useCase->execute(new GetInvoicePaymentsRequest(self::INVOICE_UUID, self::MERCHANT_ID));
    }
}
