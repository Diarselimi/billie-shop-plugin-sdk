<?php

namespace App\Application\UseCase\ExtendInvoice;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Fee\FeeService;
use App\DomainModel\Invoice\Duration;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\SalesforceInterface as DciService;
use App\DomainModel\Payment\PaymentsServiceInterface;

class ExtendInvoiceUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private InvoiceServiceInterface $invoiceService;

    private FeeService $feeService;

    private PaymentsServiceInterface $paymentsService;

    private DciService $dciService;

    public function __construct(
        InvoiceServiceInterface $invoiceService,
        FeeService $feeService,
        PaymentsServiceInterface $paymentsService,
        DciService $dciService
    ) {
        $this->invoiceService = $invoiceService;
        $this->feeService = $feeService;
        $this->paymentsService = $paymentsService;
        $this->dciService = $dciService;
    }

    public function execute(ExtendInvoiceRequest $request)
    {
        $invoiceUuid = $request->getInvoiceUuid();
        $duration = new Duration($request->getDuration());
        $invoice = $this->invoiceService->getOneByUuid($invoiceUuid);
        if (!$invoice) {
            throw new InvoiceNotFoundException('Invoice of uuid ' . $invoiceUuid . ' not found!');
        }
        if (!$invoice->canBeExtendedWith($duration)) {
            throw new InvoiceNotExtendableException('Invoice of uuid ' . $invoiceUuid . ' cannot be extended.');
        }
        if ($invoice->isLate() && $this->dciService->isDunningInProgress($invoice)) {
            throw new InvoiceNotExtendableException('Invoice of uuid ' . $invoiceUuid . ' cannot be extended.');
        }
        $this->paymentsService->extendInvoiceDuration($invoice, $duration);

        $fee = $this->feeService->getFee($invoice);

        $this->invoiceService->extendInvoiceDuration($invoice, $fee, $duration);
    }
}
