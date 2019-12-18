Feature: When I call get credentials endpoint I will get the credentials for sandbox and production
  if merchant is fully onboarded then I will get production credentials otherwise I will get sandbox only.
  If sandbox merchant is not created, sandbox credentials should not be set in the response.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_CREDENTIALS


  Scenario: Successfully I call the endpoint with production and sandbox credentials
    Given I get from Oauth service the merchant credentials
    And I get from Oauth service the merchant credentials
    And The merchant have sandbox credentials created
    And I get from sandbox service a successful response on "/api/merchant/1ac823bd-2a3e-48b0-aa61-3b95962922eb" call with body:
    """
    {
      "credentials": {"client_id": "some-uuid", "secret": "12erfwgdb32rerandom"}
    }
    """
    And I have the onboarding for merchant 1 with status "complete"
    When I send a GET request to "/merchant/credentials"
    Then the response status code should be 200
    And the json response should be:
    """
    {
      "production_credentials":{"client_id":"some_dummy_client_id","client_secret":"anotherRand0mStr1ng"},
      "sandbox_credentials":{"client_id": "some-uuid", "client_secret": "12erfwgdb32rerandom"}
    }
    """

  Scenario: Successfully I get credentials only from sandbox
    Given I get from Oauth service the merchant credentials
    And The merchant have sandbox credentials created
    And I get from sandbox service a successful response on "/api/merchant/1ac823bd-2a3e-48b0-aa61-3b95962922eb" call with body:
    """
    {
      "credentials": {"client_id": "some-uuid", "secret": "12erfwgdb32rerandom"}
    }
    """
    And I have the onboarding for merchant 1 with status "confirmation_pending"
    When I send a GET request to "/merchant/credentials"
    Then the response status code should be 200
    And the json response should be:
    """
    {
      "production_credentials":null,
      "sandbox_credentials":{"client_id": "some-uuid", "client_secret": "12erfwgdb32rerandom"}
    }
    """

  Scenario: Successfully I call the endpoint with no
    Given I have the onboarding for merchant 1 with status "new"
    When I send a GET request to "/merchant/credentials"
    Then the response status code should be 200
    And the json response should be:
    """
    {"production_credentials":null,"sandbox_credentials":null}
    """
