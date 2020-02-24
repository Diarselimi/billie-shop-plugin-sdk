<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorScoring;

use App\DomainModel\AbstractServiceRequestException;

class DebtorScoringServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'scoring';
    }
}
