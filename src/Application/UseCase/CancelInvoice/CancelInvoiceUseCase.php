<?php

declare(strict_types=1);

namespace App\Application\UseCase\CancelInvoice;

use App\Application\Exception\InvoiceNotFoundException as InvoiceNotFound;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCancellationService;
use App\DomainModel\Invoice\InvoiceContainerFactory;
use App\DomainModel\Invoice\InvoiceNotFoundException;
use App\DomainModel\Payment\PaymentsServiceInterface;

class CancelInvoiceUseCase
{
    private InvoiceCancellationService $invoiceCancelationService;

    private InvoiceContainerFactory $invoiceContainerFactory;

    private PaymentsServiceInterface $paymentsService;

    public function __construct(
        InvoiceContainerFactory $invoiceContainerFactory,
        InvoiceCancellationService $invoiceFullCancelationService
    ) {
        $this->invoiceCancelationService = $invoiceFullCancelationService;
        $this->invoiceContainerFactory = $invoiceContainerFactory;
    }

    public function execute(CancelInvoiceRequest $request): Invoice
    {
        try {
            $invoiceContainer = $this->invoiceContainerFactory->createFromInvoiceAndMerchant(
                $request->getUuid(),
                $request->getMerchantId()
            );
            $invoice = $invoiceContainer->getInvoice();
        } catch (InvoiceNotFoundException $e) {
            throw new InvoiceNotFound();
        }

        return $this->invoiceCancelationService->cancelInvoiceFully($invoice);
    }
}
