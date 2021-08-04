<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

class BankNotFoundException extends \RuntimeException
{
    protected $message = 'Bank not found';
}
