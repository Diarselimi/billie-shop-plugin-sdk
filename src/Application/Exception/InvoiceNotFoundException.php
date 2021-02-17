<?php

declare(strict_types=1);

namespace App\Application\Exception;

class InvoiceNotFoundException extends \RuntimeException
{
    protected $message = 'Invoice not found';
}
