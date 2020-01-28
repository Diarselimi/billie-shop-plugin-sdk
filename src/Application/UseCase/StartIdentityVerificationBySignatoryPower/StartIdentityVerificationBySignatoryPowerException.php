<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerificationBySignatoryPower;

class StartIdentityVerificationBySignatoryPowerException extends \RuntimeException
{
    protected $message = 'Identity verification process cannot be started.';
}
