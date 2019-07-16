<?php

namespace App\Application\Exception;

class PaymentOrderConfirmException extends \Exception
{
    protected $message = 'Order cannot be confirmed';
}
