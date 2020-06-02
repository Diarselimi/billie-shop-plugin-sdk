<?php

declare(strict_types=1);

namespace App\DomainModel\Fraud;

use App\DomainModel\AbstractServiceRequestException;

final class FraudServiceException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'fraud';
    }
}
