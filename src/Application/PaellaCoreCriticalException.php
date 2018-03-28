<?php

namespace App\Application;

class PaellaCoreCriticalException extends \RuntimeException
{
    const CODE_NOT_FOUND = 400001;

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
