<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

use App\DomainModel\AbstractServiceRequestException;

class DebtorLimitServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'limit';
    }
}
