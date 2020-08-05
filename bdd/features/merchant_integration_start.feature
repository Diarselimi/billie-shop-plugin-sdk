Feature: As a merchant that is in the onboarding process, I want start the technical integration and create a sandbox merchant

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token

  Scenario: Successful integration start call
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And The merchant have sandbox credentials created
    And I get from sandbox service a successful response on "/api/merchant/1ac823bd-2a3e-48b0-aa61-3b95962922eb" call with body:
    """
    {
      "credentials": {"client_id": "testClientId", "secret": "testClientSecret"}
    }
    """
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 200
    And the JSON should have "production_credentials"
    And the JSON should not have "production_credentials/client_id"
    And the JSON should not have "production_credentials/client_secret"
    And the JSON should have "sandbox_credentials"
    And the JSON should have "sandbox_credentials/client_id"
    And the JSON should have "sandbox_credentials/client_secret"
    And the JSON at "sandbox_credentials/client_id" should be "testClientId"
    And the JSON at "sandbox_credentials/client_secret" should be "testClientSecret"
    And a merchant exists with company ID 10 and sandbox merchant payment UUID "1ac823bd-2a3e-48b0-aa61-3b95962922eb"

  Scenario: Start integration fails if step is not in state new
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And The following onboarding steps are in states for merchant 1:
      | name                        | state    |
      | financial_assessment        | new      |
      | signatory_confirmation      | new      |
      | identity_verification       | new      |
      | ubo_pepsanctions_assessment | new      |
      | technical_integration       | complete |
      | sepa_mandate_confirmation   | new      |
      | sales_confirmation          | new      |
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Start integration not allowed","code":"forbidden"}]}
    """

  Scenario: Successful integration call if Sandbox Payment UUID is already set
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And the sandbox merchant payment UUID is already set
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 200

  Scenario: Start integration fails if Sandbox client is not available
    Given I add "X-Test" header equal to 1
    And a merchant user exists with role "admin" and permission MANAGE_ONBOARDING
    And I get from sandbox service a response on "/api/merchant/1ac823bd-2a3e-48b0-aa61-3b95962922eb" call with status code 500 body:
    """
      {"error": "unexpected error"}
    """
    When I send a POST request to "/public/merchant/start-integration"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Start integration not allowed","code":"forbidden"}]}
    """
