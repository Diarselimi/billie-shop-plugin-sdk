<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use Ozean12\Money\Money;

class CreditNoteCreationService
{
    private InvoiceServiceInterface $invoiceService;

    public function __construct(InvoiceServiceInterface $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function create(Invoice $invoice, CreditNote $creditNote): void
    {
        $this->validate($invoice, $creditNote);

        $this->invoiceService->createCreditNote($invoice, $creditNote);
    }

    private function validate(Invoice $invoice, CreditNote $creditNote): void
    {
        if ($invoice->isComplete() || $invoice->isCanceled()) {
            throw new CreditNoteNotAllowedException(
                'Credit note creation not supported for current invoice state'
            );
        }

        $maxGrossAmount = new Money(
            $invoice->getOutstandingAmount()->subtract($invoice->getMerchantPendingPaymentAmount()->getMoneyValue())
        );

        if ($creditNote->getAmount()->getGross()->greaterThan($maxGrossAmount)) {
            throw new CreditNoteAmountExceededException();
        }

        $maxTaxAmount = $invoice->getAmount()->getTax();
        if ($creditNote->getAmount()->getTax()->greaterThan($maxTaxAmount)) {
            throw new CreditNoteAmountTaxExceededException();
        }
    }
}
