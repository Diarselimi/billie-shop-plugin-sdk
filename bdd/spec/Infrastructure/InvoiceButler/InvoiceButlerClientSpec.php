<?php

namespace spec\App\Infrastructure\InvoiceButler;

use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\Infrastructure\InvoiceButler\InvoiceButlerClient;
use App\Infrastructure\InvoiceButler\InvoiceMessageFactory;
use GuzzleHttp\Client;
use Ozean12\Transfer\Message\Invoice\ExtendInvoice;
use Ozean12\Transfer\Shared\Invoice as InvoiceMessage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceButlerClientSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(InvoiceButlerClient::class);
    }

    public function let(
        Client $invoiceButlerClient,
        InvoiceFactory $invoiceFactory,
        InvoiceCreditNoteMessageFactory $creditNoteMessageFactory,
        InvoiceMessageFactory $invoiceMessageFactory,
        MessageBusInterface $messageBus,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_should_dispatch_the_extend_invoice_message(
        MessageBusInterface $messageBus,
        InvoiceMessageFactory $invoiceMessageFactory,
        Invoice $invoice,
        InvoiceMessage $invoiceMessage
    ) {
        $invoiceMessageFactory->create($invoice)->shouldBeCalled()->willReturn($invoiceMessage);

        $messageBus->dispatch(Argument::that(function ($message) {
            return $message instanceof ExtendInvoice
                && $message->getInvoice() instanceof InvoiceMessage
            ;
        }))->shouldBeCalled()->willReturn(new Envelope(new ExtendInvoice()));

        $this->extendInvoiceDuration($invoice);
    }
}
