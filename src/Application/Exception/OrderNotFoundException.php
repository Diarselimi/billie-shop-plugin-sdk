<?php

namespace App\Application\Exception;

class OrderNotFoundException extends \Exception
{
    protected $message = 'Order not found';

    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($this->message, null, $previous);
    }
}
