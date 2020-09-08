<?php

namespace App\Tests\Functional\Context;

use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseByMethod;
use donatj\MockWebServer\ResponseInterface;
use donatj\MockWebServer\ResponseStack;

trait MockServerTrait
{
    private $serviceBasePath = '/';

    public function mockRequest($uri, ResponseInterface $response)
    {
        MockServerContext::getServer()->setResponseOfPath($this->serviceBasePath . ltrim($uri, '/'), $response);
    }

    /**
     * @param string                    $requestUri
     * @param string|array|PyStringNode $responseBody
     * @param array                     $headers
     * @param int                       $status
     * @param string|null               $method
     */
    public function mockRequestWith(
        string $requestUri,
        $responseBody,
        array $headers = [],
        int $status = 200,
        ?string $method = null
    ) {
        if (is_array($responseBody)) {
            $responseBody = json_encode($responseBody);
        }

        $response = new Response((string) $responseBody, $headers, $status);
        $responseByMethod = $method ? new ResponseByMethod([$method => $response], $response) : $response;

        $this->mockRequest($requestUri, $responseByMethod);
    }

    public function mockRequestWithResponseStack(
        string $requestUri,
        array $responseBodies,
        array $headers = [],
        int $status = 200,
        ?string $method = null
    ) {
        $responsesByMethod = [];
        foreach ($responseBodies as $responseBody) {
            if (is_array($responseBody)) {
                $responseBody = json_encode($responseBody);
            }

            $response = new Response((string) $responseBody, $headers, $status);
            $responsesByMethod[] = $method ? new ResponseByMethod([$method => $response], $response) : $response;
        }

        $this->mockRequest($requestUri, new ResponseStack(...$responsesByMethod));
    }
}
