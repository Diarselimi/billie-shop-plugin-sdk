<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;
use Symfony\Component\HttpFoundation\Response;

class OAuthServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8023;

    use MockServerTrait;

    public function __construct()
    {
        $this->startServer(self::MOCK_SERVER_PORT);
    }

    /**
     * @AfterScenario
     */
    public function afterScenario()
    {
        $this->stopServer();
    }

    /**
     * @Given /^I get from Oauth service invalid token response$/
     */
    public function iGetFromOauthServiceInvalidTokenResponse()
    {
        $this->mockRequest('/oauth/authorization', new ResponseStack(
            new MockResponse('', [], Response::HTTP_UNAUTHORIZED)
        ));
    }

    /**
     * @Given /^I get from Oauth service a valid user token$/
     */
    public function iGetFromOauthServiceValidTokenResponse()
    {
        $this->mockRequest('/oauth/authorization', new ResponseStack(
            new MockResponse(json_encode(['client_id' => 'oauthClientId', 'user_id' => 'oauthUserId']))
        ));
    }

    /**
     * @Given /^I get from Oauth service a valid client token response$/
     */
    public function iGetFromOauthServiceValidTokenResponseWithClient_id()
    {
        $this->mockRequest('/oauth/authorization', new ResponseStack(
            new MockResponse(json_encode(['client_id' => 'oauthClientId', 'user_id' => null]))
        ));
    }

    /**
     * @Given I successfully create OAuth client with id :id and secret :secret
     */
    public function iSuccessfullyCreateOAuthClientWithIdAndSecretTestSecret($id, $secret)
    {
        $this->mockRequest('/clients', new ResponseStack(
            new MockResponse(json_encode(['client_id' => $id, 'client_secret' => $secret]))
        ));
    }

    /**
     * @Given I get from OAuth service :url endpoint response with status :statusCode and body:
     */
    public function iGetFromOAuthServiceEndpointResponseWithStatusAndBody($url, $statusCode, PyStringNode $response)
    {
        $this->mockRequest($url, new ResponseStack(
            new MockResponse($response, [], (int) $statusCode)
        ));
    }
}
