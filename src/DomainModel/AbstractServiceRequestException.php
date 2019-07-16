<?php

namespace App\DomainModel;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractServiceRequestException extends \RuntimeException
{
    protected $message = 'API call response to the %s service was not successful.';

    public function __construct(?TransferException $previousException = null, ?string $message = null)
    {
        parent::__construct($message ?: $this->getDefaultMessage(), 0, $previousException);
    }

    private function getDefaultMessage(): string
    {
        $subst = "'{$this->getServiceName()}'";
        $request = $this->getRequest();

        if ($request) {
            $subst .= sprintf("at '%s %s'", strtoupper($request->getMethod()), $request->getUri()->__toString());
        }

        return sprintf($this->message, $subst);
    }

    abstract public function getServiceName(): string;

    public function getRequest(): ?RequestInterface
    {
        $transferException = $this->getPrevious();

        if ($transferException instanceof RequestException) {
            return $transferException->getRequest();
        }

        return null;
    }

    public function getResponse(): ?ResponseInterface
    {
        $transferException = $this->getPrevious();

        if ($transferException instanceof RequestException) {
            return $transferException->getResponse();
        }

        return null;
    }
}
