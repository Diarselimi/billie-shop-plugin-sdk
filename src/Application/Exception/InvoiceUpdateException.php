<?php

declare(strict_types=1);

namespace App\Application\Exception;

class InvoiceUpdateException extends \RuntimeException
{
    protected $message = 'Invoice could not be updated.';
}
