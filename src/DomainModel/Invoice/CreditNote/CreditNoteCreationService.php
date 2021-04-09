<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

use App\DomainModel\Invoice\InvoiceContainer;
use App\DomainModel\Invoice\InvoiceServiceInterface;

class CreditNoteCreationService
{
    private InvoiceServiceInterface $invoiceService;

    public function __construct(InvoiceServiceInterface $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * @param  InvoiceContainer                     $invoiceContainer
     * @param  CreditNote                           $creditNote
     * @throws CreditNoteAmountExceededException
     * @throws CreditNoteAmountTaxExceededException
     * @throws CreditNoteNotAllowedException
     */
    public function create(InvoiceContainer $invoiceContainer, CreditNote $creditNote): void
    {
        $this->validate($invoiceContainer, $creditNote);

        $invoice = $invoiceContainer->getInvoice();

        // TODO return this field from butler and set it in the Invoice factory, not here (BXP-412)
        // Depends on: https://github.com/ozean12/invoice-butler/pull/71/files
        $invoice->setPaymentDebtorUuid(
            $invoiceContainer->getOrderContainer()->getDebtorCompany()->getUuid()
        );

        $this->invoiceService->createCreditNote($invoice, $creditNote);
    }

    private function validate(InvoiceContainer $invoiceContainer, CreditNote $creditNote): void
    {
        if (!$invoiceContainer->getOrder()->isWorkflowV2()) {
            throw new CreditNoteNotAllowedException(
                sprintf(
                    'Credit note creation not supported for order workflow: %s',
                    $invoiceContainer->getOrder()->getWorkflowName()
                )
            );
        }

        $invoice = $invoiceContainer->getInvoice();
        if ($invoice->isComplete() || $invoice->isCanceled()) {
            throw new CreditNoteNotAllowedException(
                'Credit note creation not supported for current invoice state'
            );
        }

        $maxGrossAmount = $invoice->getOutstandingAmount();
        if ($creditNote->getAmount()->getGross()->greaterThan($maxGrossAmount)) {
            throw new CreditNoteAmountExceededException();
        }

        $maxTaxAmount = $invoice->getAmount()->getTax();
        if ($creditNote->getAmount()->getTax()->greaterThan($maxTaxAmount)) {
            throw new CreditNoteAmountTaxExceededException();
        }
    }
}
