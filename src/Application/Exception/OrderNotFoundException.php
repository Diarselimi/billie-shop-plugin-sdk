<?php

namespace App\Application\Exception;

class OrderNotFoundException extends \Exception
{
    protected $message = 'Order not found';
}
