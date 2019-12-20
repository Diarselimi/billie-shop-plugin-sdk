<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

interface BicLookupServiceInterface
{
    public function lookup(IbanDTO $iban): string;
}
