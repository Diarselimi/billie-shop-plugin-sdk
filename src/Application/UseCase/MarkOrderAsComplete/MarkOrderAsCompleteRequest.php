<?php

declare(strict_types=1);

namespace App\Application\UseCase\MarkOrderAsComplete;

class MarkOrderAsCompleteRequest
{
    private string $invoiceUuid;

    public function __construct(string $invoiceUuid)
    {
        $this->invoiceUuid = $invoiceUuid;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }
}
