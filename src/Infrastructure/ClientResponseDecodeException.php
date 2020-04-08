<?php

namespace App\Infrastructure;

use Throwable;

class ClientResponseDecodeException extends \RuntimeException
{
    public function __construct($error = "", $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Cannot decode the response payload: %', $error);

        parent::__construct($message, $code, $previous);
    }
}
