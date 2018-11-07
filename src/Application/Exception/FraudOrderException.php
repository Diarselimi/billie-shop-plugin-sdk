<?php

namespace App\Application\Exception;

class FraudOrderException extends \Exception
{
    protected $message = 'Order was marked as fraud';
}
