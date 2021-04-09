<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use App\DomainModel\Invoice\CreditNote\CreditNote;

interface InvoiceServiceInterface
{
    public function getByUuids(array $uuids): InvoiceCollection;

    public function getOneByUuid(string $uuid): ?Invoice;

    public function createCreditNote(Invoice $invoice, CreditNote $creditNote): void;
}
