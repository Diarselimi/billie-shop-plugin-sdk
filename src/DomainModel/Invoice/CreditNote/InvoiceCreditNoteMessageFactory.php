<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

use Ozean12\Transfer\Message\CreditNote\CreateCreditNote;

class InvoiceCreditNoteMessageFactory
{
    public function create(CreditNote $creditNote): CreateCreditNote
    {
        return (new CreateCreditNote())
            ->setGrossAmount($creditNote->getAmount()->getGross()->shift(2)->toInt())
            ->setNetAmount($creditNote->getAmount()->getNet()->shift(2)->toInt())
            ->setInvoiceUuid($creditNote->getInvoiceUuid())
            ->setUuid($creditNote->getUuid())
            ->setExternalCode($creditNote->getExternalCode())
            ->setExternalComment($creditNote->getExternalComment())
            ->setInternalComment($creditNote->getInternalComment())
        ;
    }
}
