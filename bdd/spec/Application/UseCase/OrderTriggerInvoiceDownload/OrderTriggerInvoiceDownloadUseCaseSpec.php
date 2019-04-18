<?php

namespace spec\App\Application\UseCase\OrderTriggerInvoiceDownload;

use App\Application\UseCase\OrderTriggerInvoiceDownload\OrderTriggerInvoiceDownloadUseCase;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\Infrastructure\Sns\SnsInvoiceUploadHandler;
use PhpSpec\ObjectBehavior;

class OrderTriggerInvoiceDownloadUseCaseSpec extends ObjectBehavior
{
    private const ORDER_ID = 123;

    private const ORDER_EXTERNAL_CODE = "testCode";

    private const MERCHANT_ID = 2001;

    private const INVOICE_NUMBER = 'DE124087293182842194-1';

    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderTriggerInvoiceDownloadUseCase::class);
    }

    public function let(
        OrderRepositoryInterface $orderRepository,
        SnsInvoiceUploadHandler $invoiceHandler
    ) {
        $this->beConstructedWith($orderRepository, $invoiceHandler);
    }

    public function it_should_call_the_publisher_for_every_found_order_and_return_the_id_of_the_last_one(
        OrderRepositoryInterface $orderRepository,
        SnsInvoiceUploadHandler $invoiceHandler
    ) {
        $orderRepository
            ->getWithInvoiceNumber(0, 0)
            ->shouldBeCalledOnce()
            ->willReturn(
                $this->getSampleGenerator()
            );

        $invoiceHandler
            ->handleInvoice(
                self::ORDER_EXTERNAL_CODE,
                self::MERCHANT_ID,
                self::INVOICE_NUMBER,
                '/Billie_Invoice_DE124087293182842194-1.pdf',
                SnsInvoiceUploadHandler::EVENT_MIGRATION
            )->shouldBeCalledOnce()
        ;

        $this->execute(0, 0, 0, 0)->shouldReturn(self::ORDER_ID);
    }

    public function it_should_return_same_lastId_if_no_orders_found(
        OrderRepositoryInterface $orderRepository,
        SnsInvoiceUploadHandler $invoiceHandler
    ) {
        $orderRepository
            ->getWithInvoiceNumber(50, 10)
            ->shouldBeCalledOnce()
            ->willReturn(
                $this->getEmptyGenerator()
            );

        $invoiceHandler->handleInvoice()->shouldNotBeCalled();

        $this->execute(50, 0, 0, 10)->shouldReturn(10);
    }

    private function getEmptyGenerator(): \Generator
    {
        yield from [];
    }

    private function getSampleGenerator(): \Generator
    {
        yield [
            'id' => self::ORDER_ID,
            'external_code' => self::ORDER_EXTERNAL_CODE,
            'merchant_id' => self::MERCHANT_ID,
            'invoice_number' => self::INVOICE_NUMBER,
        ];
    }
}
