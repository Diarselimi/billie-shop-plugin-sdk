<?php

namespace App\Application;

class PaellaCoreCriticalException extends \RuntimeException
{
    const CODE_NOT_FOUND = 'not_found';
    const CODE_ORDER_COULD_NOT_BE_PERSISTED = 'order_persist_failed';
    const CODE_RISKY_EXCEPTION = 'risky_exception';
    const CODE_ALFRED_EXCEPTION = 'alfred_exception';
    const CODE_BORSCHT_EXCEPTION = 'borscht_exception';
    const CODE_REQUEST_DECODE_EXCEPTION = 'request_decode_failed';
    const CODE_ORDER_PRECONDITION_CHECKS_FAILED = 'order_preconditions_failed';
    const CODE_ORDER_CHECKS_FAILED = 'order_checks_failed';
    const CODE_ORDER_CANT_BE_CANCELLED = 'order_cancel_failed';
    const CODE_DEBTOR_COULD_NOT_BE_IDENTIFIED = 'debtor_not_identified';
    const CODE_DEBTOR_LIMIT_EXCEEDED = 'debtor_limit_exceeded';
    const CODE_USER_HEADER_MISSING = 'user_header_missing';

    private $responseCode;
    private $errorCode;

    public function __construct($message = "", $code = null, $responseCode = null, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->responseCode = $responseCode;
        $this->errorCode = $code;
    }

    public function getResponseCode(): ?int
    {
        return $this->responseCode;
    }

    public function getErrorCode():? string
    {
        return $this->errorCode;
    }
}
