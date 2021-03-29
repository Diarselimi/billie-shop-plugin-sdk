<?php

declare(strict_types=1);

namespace App\Application\UseCase\ConfirmInvoicePayment;

class ConfirmInvoicePaymentNotAllowedException extends \RuntimeException
{
    protected $message = 'Payments cannot be confirmed for this invoice';
}
