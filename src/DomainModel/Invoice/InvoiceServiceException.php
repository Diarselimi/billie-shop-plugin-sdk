<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use App\DomainModel\AbstractServiceRequestException;

final class InvoiceServiceException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'invoice';
    }
}
