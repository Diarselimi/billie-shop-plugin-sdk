<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class OAuthServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/smaug/';
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
     * @Given /^I get from Oauth service a request password response$/
     */
    public function iGetFromOauthServiceRequestPasswordResponse()
    {
        $this->mockRequest('/users/request-new-password', new ResponseStack(
            new MockResponse(json_encode(['token' => 'resetPasswordToken', 'user_id' => 'oauthUserId', 'email' => 'test@billie.dev']))
        ));
    }

    /**
     * @Given I get a successful OAuth client creation response
     * @Given I successfully create OAuth client with id :id and secret :secret
     * @Given I have an OAuth client with id :id and secret :secret
     */
    public function iSuccessfullyCreateOAuthClientWithIdAndSecretTestSecret(
        $id = null,
        $secret = null
    ) {
        $id = $id ?: Uuid::uuid4();
        $secret = $secret ?: Uuid::uuid4();

        $this->mockRequest(
            '/clients',
            new ResponseStack(
                new MockResponse(json_encode(['client_id' => $id, 'client_secret' => $secret]))
            )
        );
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
     * @Given I get from Oauth service a not valid credentials response
     */
    public function iGetFromOauthServiceANotValidCredentialsResponse()
    {
        $this->mockRequest('/client/oauthClientId/credentials', new ResponseStack(
            new MockResponse('', [], 404)
        ));
    }

    /**
     * @Given I get from Oauth service a valid credentials response
     */
    public function iGetFromOauthServiceAValidCredentialsResponse()
    {
        $this->mockRequest('/client/oauthClientId/credentials', new ResponseStack(
            new MockResponse(json_encode([
                'client_id' => '1234-1244-4122-asd123',
                'secret' => '21ergfhgferetr3425tregdf',
            ]), [], 200)
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
     * @When I get a response from Authentication Service from endpoint :endpoint with status code :code
     */
    public function iGetAResponseFromAuthenticationServiceWithStatusCode($endpoint, $statusCode)
    {
        $this->mockRequest($endpoint, new MockResponse('{}', [], $statusCode));
    }
}
