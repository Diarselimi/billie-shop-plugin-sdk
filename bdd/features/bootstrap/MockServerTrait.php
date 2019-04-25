<?php

use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseByMethod;
use donatj\MockWebServer\ResponseInterface;

trait MockServerTrait
{
    /**
     * @var MockWebServer
     */
    private $server;

    public function startServer(int $port = 8021)
    {
        $this->server = new MockWebServer($port);
        $this->server->start();
    }

    public function stopServer()
    {
        $this->server->stop();
    }

    public function mockRequest($uri, ResponseInterface $response)
    {
        $this->server->setResponseOfPath($uri, $response);
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
