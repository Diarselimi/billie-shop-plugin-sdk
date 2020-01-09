<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerification;

class StartIdentityVerificationException extends \RuntimeException
{
    protected $message = 'Identity verification process cannot be started.';
}
