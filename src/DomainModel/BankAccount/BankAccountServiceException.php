<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

class BankAccountServiceException extends \RuntimeException
{
    protected $message = 'Bank account service call was not successful';
}
