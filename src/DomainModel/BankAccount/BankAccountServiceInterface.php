<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use Ozean12\BancoSDK\Model\Bank;

interface BankAccountServiceInterface
{
    public function getBankByBic(string $bic): Bank;
}
