Feature: API Endpoint for "GET /merchant/onboarding" (ticket APIS-1635)

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a GET request to "/merchant/onboarding"
    Then the response status code should be 401
    And the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: Authenticated user without VIEW_ONBOARDING permission fails to call GET /merchant/onboarding
    Given a merchant user exists with permission FOOBAR
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a GET request to "/merchant/onboarding"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Successful authorised call to GET /foo
    Given a merchant user exists with permission VIEW_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a GET request to "/merchant/onboarding"
    Then the JSON response should be:
    """
    {
      "onboarding_state": "new",
      "onboarding_steps": [
        {
          "uuid": "*",
          "name": "financial_assessment",
          "state": "new"
        },
        {
          "uuid": "*",
          "name": "signatory_confirmation",
          "state": "new"
        },
        {
          "uuid": "*",
          "name": "identity_verification",
          "state": "new"
        },
        {
          "uuid": "*",
          "name": "technical_integration",
          "state": "new"
        },
        {
          "uuid": "*",
          "name": "sepa_mandate_confirmation",
          "state": "new"
        }
      ]
    }
    """
    And the JSON should have "onboarding_steps/0/uuid"
    And the JSON should have "onboarding_steps/4/uuid"
    And the response status code should be 200
