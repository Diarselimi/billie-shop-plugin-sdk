<?php

use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseByMethod;
use donatj\MockWebServer\ResponseInterface;

trait MockServerTrait
{
    private $serviceBasePath = '/';

    public function mockRequest($uri, ResponseInterface $response)
    {
        MockServerContext::getServer()->setResponseOfPath($this->serviceBasePath . ltrim($uri, '/'), $response);
    }

    /**
     * @param string                    $uri
     * @param string|array|PyStringNode $body
     * @param array                     $headers
     * @param int                       $status
     * @param string|null               $method
     */
    public function mockRequestWith(string $uri, $body, array $headers = [], int $status = 200, ?string $method = null)
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        $response = new Response((string) $body, $headers, $status);
        $responseByMethod = $method ? new ResponseByMethod([$method => $response], $response) : $response;

        $this->mockRequest($uri, $responseByMethod);
    }
}
