<?php

namespace spec\App\Application\UseCase\OrderTriggerInvoiceDownload;

use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\OrderTriggerInvoiceDownload\OrderTriggerInvoiceDownloadUseCase;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\Infrastructure\Sns\InvoiceDownloadEventPublisherInterface;
use PhpSpec\ObjectBehavior;

class OrderTriggerInvoiceDownloadUseCaseSpec extends ObjectBehavior
{
    private const ORDER_ID = 123;

    private const MERCHANT_ID = 2001;

    private const INVOICE_NUMBER = 'DE124087293182842194-1';

    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderTriggerInvoiceDownloadUseCase::class);
    }

    public function let(
        OrderRepositoryInterface $orderRepository,
        InvoiceDownloadEventPublisherInterface $eventPublisher
    ) {
        $this->beConstructedWith($orderRepository, $eventPublisher);
    }

    public function it_should_call_the_publisher_for_every_found_order_and_return_the_id_of_the_last_one(
        OrderRepositoryInterface $orderRepository,
        InvoiceDownloadEventPublisherInterface $eventPublisher
    ) {
        $orderRepository
            ->getWithInvoiceNumber(0, 0)
            ->shouldBeCalledOnce()
            ->willReturn(
                $this->getSampleGenerator()
            )
        ;

        $eventPublisher->publish(
            self::ORDER_ID,
            self::MERCHANT_ID,
            self::INVOICE_NUMBER
        )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->execute(0, 0)->shouldReturn(self::ORDER_ID);
    }

    public function it_should_return_same_lastId_if_no_orders_found(
        OrderRepositoryInterface $orderRepository,
        InvoiceDownloadEventPublisherInterface $eventPublisher
    ) {
        $orderRepository
            ->getWithInvoiceNumber(50, 10)
            ->shouldBeCalledOnce()
            ->willReturn(
                $this->getEmptyGenerator()
            )
        ;

        $eventPublisher->publish()->shouldNotBeCalled();

        $this->execute(50, 10)->shouldReturn(10);
    }

    public function it_should_throw_exception_if_publisher_failed(
        OrderRepositoryInterface $orderRepository,
        InvoiceDownloadEventPublisherInterface $eventPublisher
    ) {
        $orderRepository
            ->getWithInvoiceNumber(0, 0)
            ->shouldBeCalledOnce()
            ->willReturn(
                $this->getSampleGenerator()
            )
        ;

        $eventPublisher->publish(
            self::ORDER_ID,
            self::MERCHANT_ID,
            self::INVOICE_NUMBER
        )
            ->shouldBeCalledTimes(1)
            ->willReturn(false)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [0, 0]);
    }

    private function getEmptyGenerator(): \Generator
    {
        yield from [];
    }

    private function getSampleGenerator(): \Generator
    {
        yield [
            'id' => self::ORDER_ID,
            'merchant_id' => self::MERCHANT_ID,
            'invoice_number' => self::INVOICE_NUMBER,
        ];
    }
}
