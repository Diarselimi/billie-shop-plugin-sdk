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
    private static $server;

    public static function startServer(int $port)
    {
        self::$server = new MockWebServer($port);
        self::$server->start();
    }

    public static function stopServer()
    {
        self::$server->stop();
    }

    public function mockRequest($uri, ResponseInterface $response)
    {
        self::$server->setResponseOfPath($uri, $response);
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
