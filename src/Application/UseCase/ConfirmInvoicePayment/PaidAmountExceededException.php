<?php

declare(strict_types=1);

namespace App\Application\UseCase\ConfirmInvoicePayment;

class PaidAmountExceededException extends \Exception
{
    protected $message = 'The confirmed paid amount cannot be higher than the invoice outstanding amount.';
}
