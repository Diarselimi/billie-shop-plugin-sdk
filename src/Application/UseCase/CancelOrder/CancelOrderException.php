<?php

namespace App\Application\UseCase\CancelOrder;

class CancelOrderException extends \RuntimeException
{
    protected $message = 'The order cannot be canceled';

    protected $code = 'order_cancel_failed';
}
