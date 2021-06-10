<?php

namespace spec\App\DomainModel\Invoice;

use App\DomainModel\Invoice\ExtendInvoiceService;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Invoice\UpdateInvoiceFeeService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class ExtendInvoiceServiceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ExtendInvoiceService::class);
    }

    public function let(
        InvoiceServiceInterface $invoiceService,
        UpdateInvoiceFeeService $updateInvoiceFeeService,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_should_call_the_client(
        InvoiceServiceInterface $invoiceService,
        UpdateInvoiceFeeService $updateInvoiceFeeService,
        Invoice $invoice,
        OrderContainer $orderContainer
    ) {
        $oldDuration = 15;
        $newDuration = 30;
        $invoice->getDueDate()->shouldBeCalled()->willReturn(new \DateTime('2022-10-01'));
        $invoice->setDueDate(new \DateTime('2022-10-16'))->shouldBeCalled()->willReturn($invoice);
        $invoice->getDuration()->shouldBeCalled()->willReturn($oldDuration);
        $invoice->setDuration($newDuration)->shouldBeCalled()->willReturn($invoice);

        $updateInvoiceFeeService->updateFee($orderContainer, $invoice)->shouldBeCalledOnce();
        $invoiceService->extendInvoiceDuration($invoice)->shouldBeCalledOnce();

        $this->extend($orderContainer, $invoice, $newDuration);
    }
}
