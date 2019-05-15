<?php

namespace App\Infrastructure\Alfred;

use App\Application\PaellaCoreCriticalException;

class AlfredRequestException extends PaellaCoreCriticalException
{
    public const DEFAULT_ERROR_MESSAGE = 'Alfred request failed';

    public function __construct(?int $httpStatusCode = null, \Exception $previous = null)
    {
        $message = is_null($httpStatusCode) ? self::DEFAULT_ERROR_MESSAGE :
            sprintf(self::DEFAULT_ERROR_MESSAGE . 'with status code %d', $httpStatusCode);

        parent::__construct($message, self::CODE_ALFRED_EXCEPTION, null, $previous);
    }
}
