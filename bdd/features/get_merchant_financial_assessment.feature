Feature: Get financial assessments feature.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token

  Scenario: Successfully I call the endpoint with the correct data provided
    Given I have the following Financial Assessment Data:
    """
    {"data":123.33, "next":22.22}
    """
    When I send a GET request to "/merchant/financial-assessment"
    Then the response status code should be 200
    And the json response should be:
    """
    {"data":123.33, "next":22.22}
    """

  Scenario: There are no data for the current merchant
    When I send a GET request to "/merchant/financial-assessment"
    Then the response status code should be 404

