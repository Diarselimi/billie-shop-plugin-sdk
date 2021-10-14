<?php

declare(strict_types=1);

namespace App\DomainModel\Payment;

final class BankTransactionNotFoundException extends \RuntimeException
{
    protected $message = 'Bank Transaction Not Found';
}
