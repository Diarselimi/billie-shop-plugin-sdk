<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

use App\DomainModel\AbstractServiceRequestException;

class IdentityVerificationServiceException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'identity-verification';
    }
}
