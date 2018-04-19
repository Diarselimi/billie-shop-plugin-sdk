<?php

namespace App\Application;

class PaellaCoreCriticalException extends \RuntimeException
{
    const CODE_NOT_FOUND = 400001;
    const CODE_ORDER_COULD_NOT_BE_PERSISTED = 400002;
    const CODE_RISKY_EXCEPTION = 400003;
    const CODE_REQUEST_DECODE_EXCEPTION = 400004;
    const CODE_ORDER_PRECONDITION_CHECKS_FAILED = 400005;

    private $responseCode;

    public function __construct($message = "", $code = 0, $responseCode = null, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->responseCode = $responseCode;
    }

    public function getResponseCode():? int
    {
        return $this->responseCode;
    }
}
