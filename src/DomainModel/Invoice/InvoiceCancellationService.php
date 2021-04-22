<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\CreditNote\CreditNoteNotAllowedException;
use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceCancellationService
{
    private MessageBusInterface $messageBus;

    private CreditNoteFactory $creditNoteFactory;

    private InvoiceCreditNoteMessageFactory $creditNoteMessageFactory;

    public function __construct(
        MessageBusInterface $messageBus,
        CreditNoteFactory $creditNoteFactory,
        InvoiceCreditNoteMessageFactory $creditNoteMessageFactory
    ) {
        $this->messageBus = $messageBus;
        $this->creditNoteFactory = $creditNoteFactory;
        $this->creditNoteMessageFactory = $creditNoteMessageFactory;
    }

    public function cancelInvoiceFully(Invoice $invoice): Invoice
    {
        if ($invoice->isCanceled() || $invoice->isComplete()) {
            throw new CreditNoteNotAllowedException();
        }

        $amount = new TaxedMoney(
            $invoice->getAmount()->getGross()->subtract($invoice->getCreditNotes()->getGrossSum()),
            $invoice->getAmount()->getNet()->subtract($invoice->getCreditNotes()->getNetSum()),
            $invoice->getAmount()->getTax()->subtract($invoice->getCreditNotes()->getTaxSum()),
        );

        $creditNote = $this->creditNoteFactory->create(
            $invoice,
            $amount,
            $invoice->getExternalCode() . CreditNote::EXTERNAL_CODE_SUFFIX,
            CreditNote::INTERNAL_COMMENT_CANCELATION
        );

        $this->messageBus->dispatch($this->creditNoteMessageFactory->create($creditNote));
        $invoice->getCreditNotes()->add($creditNote);

        return $invoice;
    }
}
