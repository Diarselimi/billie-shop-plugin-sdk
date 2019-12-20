<?php

declare(strict_types=1);

namespace App\Application\UseCase\SetupMerchantBankAccount;

class SetupMerchantBankAccountMissingBicException extends \RuntimeException
{
    protected $message = 'BIC code cannot be found out of the given IBAN.';
}
