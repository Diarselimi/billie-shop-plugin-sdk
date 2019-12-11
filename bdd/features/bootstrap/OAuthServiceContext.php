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
        register_shutdown_function(function () {
            self::stopServer();
        });
    }

    /**
     * @BeforeSuite
     */
    public static function beforeSuite()
    {
        self::startServer(self::MOCK_SERVER_PORT);
    }

    /**
     * @AfterSuite
     */
    public static function afterSuite()
    {
        self::stopServer();
    }

    /**
     * @Given I get from Oauth service the merchant credentials
     */
    public function iGetFromOauthServiceTheMerchantCredentials()
    {
        $this->mockRequest("/client/oauthClientId/credentials", new ResponseStack(
            new MockResponse(json_encode(['client_id' => 'some_dummy_client_id', 'secret' => 'anotherRand0mStr1ng']), [], Response::HTTP_OK)
        ));
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
            new MockResponse(json_encode(['client_id' => 'oauthClientId', 'user_id' => 'oauthUserId', 'email' => 'test@billie.dev']))
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
     * @Given I successfully create OAuth client with email :email and user id :userUuid
     */
    public function iSuccessfullyCreateOAuthClientWithEmailAndUserIdTestSecret($email, $userId)
    {
        $this->mockRequest('/users', new ResponseStack(
            new MockResponse(json_encode(['user_id' => $userId, 'user_email' => $email]))
        ));
    }

    /**
     * @Given I get from OAuth client the following list of users:
     */
    public function iSuccessfullyGetFromOAuthClientAListOfUsers(PyStringNode $response)
    {
        $this->mockRequest('/users', new ResponseStack(
            new MockResponse($response->__toString())
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

    /**
     * @Given /^I get from Oauth service invalid credentials response$/
     */
    public function andIGetFromOauthServiceInvalidCredentialsResponse()
    {
        $this->mockRequest('/oauth/token', new ResponseStack(
            new MockResponse('', [], 401)
        ));
    }

    /**
     * @Given /^I successfully obtain token from oauth service$/
     */
    public function andIGetFromOauthServiceValidTokenResponse()
    {
        $this->mockRequest('/oauth/token', new ResponseStack(
            new MockResponse(
                json_encode(
                    [
                    'token_type' => 'bearer',
                    'expires_in' => 3600,
                    'access_token' => 'testToken',
                    'refresh_token' => 'testRefreshToken',
                ]
            )
            )
        ));
    }

    /**
     * @Given /^I get from Oauth service revoke token endpoint a successful response$/
     */
    public function iGetFromOauthServiceRevokeTokenEndpointASuccessfulResponse()
    {
        $this->mockRequest('/oauth/token/revoke', new ResponseStack(
            new MockResponse('', [], 200)
        ));
    }

    /**
     * @When I will get a response from Authentication Service from endpoint :endpoint with status code :code
     */
    public function iWillGetAResponseFromAuthenticationServiceWithStatusCode($endpoint, $statusCode)
    {
        $this->mockRequest($endpoint, new ResponseStack(
            new MockResponse('', [], $statusCode)
        ));
    }
}
