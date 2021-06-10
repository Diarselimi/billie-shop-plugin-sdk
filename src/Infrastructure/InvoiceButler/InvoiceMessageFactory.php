<?php

declare(strict_types=1);

namespace App\Infrastructure\InvoiceButler;

use App\DomainModel\Invoice\Invoice;
use Ozean12\Transfer\Shared\Invoice as InvoiceMessage;

class InvoiceMessageFactory
{
    public function create(Invoice $invoice): InvoiceMessage
    {
        return (new InvoiceMessage())
            ->setUuid($invoice->getUuid())
            ->setDueDate($invoice->getDueDate()->format('Y-m-d'))
            ->setFeeRate($invoice->getFeeRate()->shift(2)->toInt())
            ->setNetFeeAmount($invoice->getFeeAmount()->getNet()->shift(2)->toInt())
            ->setVatOnFeeAmount($invoice->getFeeAmount()->getTax()->shift(2)->toInt())
            ->setDuration($invoice->getDuration())
            ->setInvoiceReferences(
                ['external_code' => $invoice->getExternalCode()]
            );
    }
}
