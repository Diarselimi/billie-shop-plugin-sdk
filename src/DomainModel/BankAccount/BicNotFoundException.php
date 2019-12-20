<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

class BicNotFoundException extends \RuntimeException
{
    protected $message = "BIC code not found.";
}
