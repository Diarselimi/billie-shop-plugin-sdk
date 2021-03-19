<?php

declare(strict_types=1);

namespace App\Application\UseCase\MarkOrderAsPaidOutV1;

class MarkOrderAsPaidOutV1Request
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
