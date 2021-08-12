<?php

namespace App\Application\UseCase\CheckoutProvideIban;

class CheckoutProvideIbanNotAllowedException extends \RuntimeException
{
    protected $message = 'IBAN is not allowed';
}
