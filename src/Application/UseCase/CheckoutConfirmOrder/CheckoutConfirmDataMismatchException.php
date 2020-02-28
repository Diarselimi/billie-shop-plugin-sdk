<?php

namespace App\Application\UseCase\CheckoutConfirmOrder;

class CheckoutConfirmDataMismatchException extends \RuntimeException
{
    protected $message = 'The provided data does not match the one from the initial checkout order creation.';
}
