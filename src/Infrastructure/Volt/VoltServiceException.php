<?php

declare(strict_types=1);

namespace App\Infrastructure\Volt;

use App\DomainModel\AbstractServiceRequestException;

final class VoltServiceException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'volt';
    }
}
