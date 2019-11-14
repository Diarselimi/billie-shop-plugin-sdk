Feature: Get invited merchant user info by token

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Request with non-existing token fails
    When I send a GET request to "/public/merchant/users/invitations/4b4e2b8b859a45bebfe0ae88b58c333b/details"
    Then the response status code should be 401
    And the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: successfully get merchant user info via invitation
    Given I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    And an invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" and status "pending" exists for email "dev@billie.dev" and role ID 1
    When I send a GET request to "/public/merchant/users/invitations/4b4e2b8b859a45bebfe0ae88b58c333b/details"
    Then the JSON response should be:
    """
      {"email":"dev@billie.dev"}
    """
    And the response status code should be 200
