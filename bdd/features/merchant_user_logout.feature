Feature: Enable merchant users to logout

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a POST request to "/merchant/user/logout"
    Then the response status code should be 401
    And the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: Successfully logout
    Given a merchant user exists with permission AUTHENTICATED_AS_MERCHANT
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/public/merchant/user/logout"
    Then the response status code should be 200
    And the response should be empty
