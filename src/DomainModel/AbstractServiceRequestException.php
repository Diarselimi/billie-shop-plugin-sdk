<?php

namespace App\DomainModel;

use App\Infrastructure\ClientResponseDecodeException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractServiceRequestException extends \RuntimeException
{
    private $defaultMessage = 'API call response to the service %s was not successful.';

    public function __construct(?\Throwable $previousException = null, ?string $message = null)
    {
        $this->message = $this->buildMessage($message, $previousException);
        parent::__construct($this->message, 0, $previousException);
    }

    private function buildMessage(?string $message = null, ?\Throwable $previous = null): string
    {
        $message = $message ? "{$message} // {$this->defaultMessage}" : $this->defaultMessage;
        $subst = "'{$this->getServiceName()}'";

        if ($previous instanceof ClientResponseDecodeException) {
            return sprintf($message, $subst) . ' ' . $previous->getMessage();
        }

        if (!$previous instanceof RequestException) {
            return sprintf($message, $subst);
        }

        $request = $previous->getRequest();
        $response = $previous->getResponse();

        if ($request) {
            $subst .= sprintf(" to '%s %s'", strtoupper($request->getMethod()), $request->getUri()->__toString());
        }

        if ($response) {
            $message .= sprintf(
                " Response was HTTP %s with body: %s",
                $response->getStatusCode(),
                $response->getBody() . ''
            );
        }

        return sprintf($message, $subst);
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
