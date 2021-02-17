<?php

declare(strict_types=1);

namespace spec\App\Application\UseCase\GetInvoice;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoice\Factory\GetInvoiceResponseFactory;
use App\Application\UseCase\GetInvoice\GetInvoiceRequest;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetInvoiceUseCaseSpec extends ObjectBehavior
{
    public function let(
        InvoiceServiceInterface $invoiceButler,
        GetInvoiceResponseFactory $responseFactory
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function itShouldThrowInvoiceNotFoundException(
        InvoiceServiceInterface $invoiceService,
        Invoice $invoice
    ) {
        $invoiceService->getOneByUuid(Argument::any())->willReturn(null);

        $this->shouldThrow(InvoiceNotFoundException::class)
            ->during('execute', [new GetInvoiceRequest('some-uuid')]);
    }
}
