<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use Ozean12\BancoSDK\Model\Bank;
use Ozean12\Support\ValueObject\Iban;

interface BankAccountServiceInterface
{
    public function getBankByBic(string $bic): Bank;

    public function getBankByIban(Iban $iban): Bank;
}
