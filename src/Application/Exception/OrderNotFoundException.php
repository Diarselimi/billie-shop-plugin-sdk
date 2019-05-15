<?php

namespace App\Application\Exception;

use Throwable;

class OrderNotFoundException extends \Exception
{
    public const MESSAGE = 'Order not found';

    public function __construct($message = self::MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
