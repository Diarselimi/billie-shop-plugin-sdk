<?php

namespace App\Application\UseCase\CheckoutProvideIban;

class CheckoutProvideIbanFailedException extends \RuntimeException
{
    protected $message = 'Something went wrong';
}
