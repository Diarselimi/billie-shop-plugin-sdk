<?php

namespace App\Application\Exception;

/**
 * @deprecated Use ConfirmInvoicePayment* classes
 */
class PaymentOrderConfirmException extends \Exception
{
    protected $message = 'Order cannot be confirmed';
}
