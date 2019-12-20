<?php

declare(strict_types=1);

namespace App\Application\UseCase\SetupMerchantBankAccount;

class SetupMerchantBankAccountException extends \RuntimeException
{
    protected $message = 'Bank Account setup failed.';
}
