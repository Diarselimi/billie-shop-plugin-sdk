<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use App\Infrastructure\FinTechToolbox\FinTechToolboxResponseDTO;

interface BicLookupServiceInterface
{
    public function lookup(IbanDTO $iban): FinTechToolboxResponseDTO;
}
