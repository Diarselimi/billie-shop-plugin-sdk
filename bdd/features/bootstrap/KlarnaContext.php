<?php

namespace App\Tests\Functional\Context;

use App\Kernel;
use Behat\Behat\Context\Context;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use PHPUnit\Framework\Assert;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KlarnaContext implements Context
{
    private const REQUEST_PATH_PREFIX = '/klarna/scheme';

    private Kernel $kernel;

    private PdoConnection $pdo;

    private Response $response;

    private string $generatedToken;

    /**
     * @beforeScenario
     */
    public function bootKernel(): void
    {
        $dotEnv = new Dotenv();
        $dotEnv->load(__DIR__.'/../../../.env');

        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();
        $this->pdo = $this->kernel->getContainer()->get('billie_pdo.default_connection');
    }

    /**
     * @When I request :endpoint
     * @When I request :endpoint with body:
     */
    public function request(string $endpoint, string $body = null): void
    {
        $request = $this->createRequest($endpoint, $body);

        $this->response = $this->kernel->handle($request);

        $this->kernel->terminate($request, $this->response);
    }

    /**
     * @Then the response is :statusCode
     */
    public function assertResponseStatusCode(int $statusCode): void
    {
        Assert::assertEquals($statusCode, $this->response->getStatusCode());
    }

    /**
     * @Then the response is :statusCode with body:
     */
    public function assertResponse(int $statusCode, string $body): void
    {
        Assert::assertEquals($statusCode, $this->response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString($body, $this->response->getContent());
    }

    /**
     * @Then the response is :statusCode with a token in the field :field
     */
    public function assertResponseWithToken(int $statusCode, string $tokenField): void
    {
        Assert::assertEquals($statusCode, $this->response->getStatusCode());

        $body = json_decode($this->response->getContent(), true);
        $this->generatedToken = $body[$tokenField] ?? null;

        Assert::assertNotNull($tokenField);
    }

    /**
     * @Then a checkout session was saved with the returned token
     */
    public function assertCheckoutSessionWasSaved(): void
    {
        $checkoutSessions = $this->pdo
            ->query("SELECT * FROM checkout_sessions WHERE uuid = '{$this->generatedToken}'")
            ->fetchAll();

        Assert::assertCount(1, $checkoutSessions);
    }

    private function createRequest(string $endpoint, ?string $body): Request
    {
        [$method, $path] = explode(' ', $endpoint);

        return Request::create(self::REQUEST_PATH_PREFIX.$path, $method, [], [], [], [], $body);
    }
}
