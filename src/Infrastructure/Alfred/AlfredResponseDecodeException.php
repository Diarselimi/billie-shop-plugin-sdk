<?php

namespace App\Infrastructure\Alfred;

use App\Application\PaellaCoreCriticalException;

class AlfredResponseDecodeException extends PaellaCoreCriticalException
{
    private const DEFAULT_ERROR_MESSAGE = 'Alfred response body cannot be decoded';

    public function __construct()
    {
        parent::__construct(self::DEFAULT_ERROR_MESSAGE, self::CODE_ALFRED_EXCEPTION);
    }
}
