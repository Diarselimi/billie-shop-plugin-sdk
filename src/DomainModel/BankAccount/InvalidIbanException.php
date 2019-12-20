<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

class InvalidIbanException extends \Exception
{
    protected $message = 'Value is an invalid IBAN.';
}
