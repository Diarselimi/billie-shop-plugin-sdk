<?php

namespace App\Application\Exception;

class CheckoutSessionConfirmException extends \RuntimeException
{
    protected $message = 'The order amount and duration failed while confirming.';
}
