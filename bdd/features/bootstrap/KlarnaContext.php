<?php

namespace App\Tests\Functional\Context;

use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class KlarnaContext implements Context
{
    protected KernelInterface $kernel;

    private const REQUEST_PATH_PREFIX = '/klarna/scheme';

    private PdoConnection $pdo;

    private Response $response;

    private ?string $generatedToken = null;

    private array $headers = [];

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @beforeScenario
     */
    public function bootKernel(): void
    {
        $this->pdo = $this->kernel->getContainer()->get('billie_pdo.default_connection');
    }

    /**
     * @When I request :endpoint
     * @When I request :endpoint with body:
     */
    public function sendRequest(string $endpoint, string $body = null): void
    {
        $request = $this->createRequest($endpoint, $body);
        $request->headers->add($this->headers);
        $this->response = $this->kernel->handle($request);

        $this->kernel->terminate($request, $this->response);
    }

    /**
     * @When I save order uuid
     */
    public function setOrderUuid(): void
    {
        $orderContainer = $this->kernel->getContainer()->get(OrderContainerFactory::class);
        $this->generatedToken = $orderContainer->getCachedOrderContainer()->getOrder()->getUuid();
    }

    /**
     * @Then the response is :statusCode with empty body
     * @Then the response is :statusCode with body:
     */
    public function assertResponseWithBody(int $statusCode, string $body = '{}'): void
    {
        $body = $this->prepareExpectedJsonContent($body);

        Assert::assertEquals($statusCode, $this->response->getStatusCode());
        Assert::assertJsonStringEqualsJsonString($body, $this->response->getContent());
    }

    /**
     * Add an header element
     *
     * @Then I add header :name with :value
     */
    public function iAddHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * @Then print response
     */
    public function printResponse(): void
    {
        echo $this->response->getContent();
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

    /**
     * @Then there should be the following registered orders:
     */
    public function assertOrder(TableNode $table): void
    {
        $uuidsToBeSelected = [];
        $expectedDbEntries = [];

        foreach ($table as $row) {
            $uuidsToBeSelected[] = $row['Id'];
            $expectedDbEntries[] = [
                'uuid' => $row['Id'],
                'external_code' => $row['External Id'],
                'expiration' => $row['Expires At'],
                'state' => $row['State'],
            ];
        }

        $columns = array_keys($expectedDbEntries[0]);
        $columns = implode(', ', $columns);
        $uuids = '\'' . implode('\',  \'', $uuidsToBeSelected) . '\'';

        $actualDbEntries = $this->pdo
            ->query("SELECT $columns FROM orders WHERE uuid IN ($uuids)")
            ->fetchAll(\PDO::FETCH_ASSOC);

        Assert::assertEquals($expectedDbEntries, $actualDbEntries);
    }

    private function createRequest(string $endpoint, ?string $body): Request
    {
        [$method, $path] = explode(' ', $endpoint);

        return Request::create(self::REQUEST_PATH_PREFIX . $path, $method, [], [], [], [], $body);
    }

    private function prepareExpectedJsonContent(string $body): string
    {
        $replacements = [
            '{order_uuid}' => $this->generatedToken,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }
}
