<?php

declare(strict_types=1);

namespace App\Application\UseCase\ResetPassword;

class ResetPasswordException extends \RuntimeException
{
    protected $message = 'Password reset failed';
}
