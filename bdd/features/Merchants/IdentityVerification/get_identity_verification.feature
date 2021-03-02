Feature: Get identity verification

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Specific case data from company service is returned in response
    Given The following onboarding steps are in states for merchant 1:
      | name                        | state                |
      | identity_verification       | confirmation_pending |
    And I get from Oauth service a valid user token
    And I get from companies service identity verification response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    And a merchant user exists with permission MANAGE_ONBOARDING and identity verification case uuid "d46af5c4-7f78-494e-8005-d732310e3641"
    When I send a GET request to "/merchant/user/identity-verification"
    Then the JSON response should be:
    """
    {
      "url": "https://postident.deutschepost.de/identportal/?vorgangsnummer=0AAAAAA0AAAA",
      "valid_till": "2050-01-01 00:00:00",
      "case_status": "new",
      "identification_status": null
    }
    """
    And the response status code should be 200
