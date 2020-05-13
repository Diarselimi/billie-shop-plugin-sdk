<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder;

class CheckoutConfirmDataMismatchException extends \RuntimeException
{
    protected $message = 'The provided data does not match the data from the initial checkout order creation.';
}
