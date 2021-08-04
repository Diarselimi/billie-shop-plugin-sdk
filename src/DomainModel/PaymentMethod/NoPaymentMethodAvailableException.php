<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

class NoPaymentMethodAvailableException extends \RuntimeException
{
    protected $message = "No payment method is available for this order / invoice";
}
