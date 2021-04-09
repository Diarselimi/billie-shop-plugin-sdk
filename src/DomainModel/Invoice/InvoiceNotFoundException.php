<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

class InvoiceNotFoundException extends \RuntimeException
{
    protected $message = 'Invoice not found';
}
