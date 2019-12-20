<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use App\DomainModel\AbstractServiceRequestException;

class BicLookupServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'bic_lookup';
    }
}
