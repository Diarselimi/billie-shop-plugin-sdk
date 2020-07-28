<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantUserIdentityVerification;

class InconsistentIdentityVerificationCaseException extends \RuntimeException
{
    protected $message = 'Case data inconsistent';
}
