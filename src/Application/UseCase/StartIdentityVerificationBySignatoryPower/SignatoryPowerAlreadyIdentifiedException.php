<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerificationBySignatoryPower;

class SignatoryPowerAlreadyIdentifiedException extends \RuntimeException
{
    protected $message = 'This Signatory Power has been already successfully identified';
}
