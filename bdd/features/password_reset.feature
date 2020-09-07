Feature: Password reset

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Endpoint to request new password returns 200
    Given I get from Oauth service a request password response
    When I send a POST request to "/public/merchant/user/request-new-password" with body:
    """
    {"email": "test@billie.dev"}
    """
    Then the response status code should be 204
    And the response should be empty
