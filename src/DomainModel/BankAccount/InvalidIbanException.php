<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

/**
 * @deprecated
 * @see \Ozean12\Support\ValueObject\Exception\InvalidIbanException
 */
class InvalidIbanException extends \Exception
{
    protected $message = 'Value is an invalid IBAN.';
}
