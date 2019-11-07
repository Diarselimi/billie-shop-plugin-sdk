Feature: APIS-1443 - Revoke user invitation

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a DELETE request to "/merchant/users/invitations/test_uuid"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Authenticated user without MANAGE_USERS permission cannot revoke invitation
    Given a merchant user exists with permission VIEW_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a DELETE request to "/merchant/users/invitations/test_uuid"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Successfully invitation revoke
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a role of name "cat-food" with uuid "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b" and permissions
    """
      ["TEST"]
    """
    And an invitation with uuid "4b4e2b8b-859a-45be-bfe0-ae88b58c333b" and status "pending" exists for email "dev@billie.dev" and role ID 1
    When I send a DELETE request to "/merchant/users/invitations/4b4e2b8b-859a-45be-bfe0-ae88b58c333b"
    Then the response status code should be 204

  Scenario: Cannot revoke invitation with invalid uuid
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a DELETE request to "/merchant/users/invitations/test_uuid"
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
        "errors":[
          {"title":"This is not a valid UUID.","code":"request_validation_error","source":"uuid"}
        ]
      }
    """

  Scenario: Revoke invitation fails if it does not exist
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a DELETE request to "/merchant/users/invitations/72b93a4b-757a-45be-98e0-2244678a300b"
    Then the response status code should be 404

  Scenario: Revoke invitation fails if it is already revoked
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a role of name "dog-food" with uuid "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b" and permissions
    """
      ["TEST"]
    """
    And an invitation with uuid "4b4e2b8b-859a-45be-bfe0-ae88b58c333b" and status "revoked" exists for email "dev@billie.dev" and role ID 1
    When I send a DELETE request to "/merchant/users/invitations/4b4e2b8b-859a-45be-bfe0-ae88b58c333b"
    Then the response status code should be 404

  Scenario: Revoke invitation fails if it is expired
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a role of name "dog-food" with uuid "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b" and permissions
    """
      ["TEST"]
    """
    And an invitation with uuid "4b4e2b8b-859a-45be-bfe0-ae88b58c333b" and status "expired" exists for email "dev@billie.dev" and role ID 1
    When I send a DELETE request to "/merchant/users/invitations/4b4e2b8b-859a-45be-bfe0-ae88b58c333b"
    Then the response status code should be 404

  Scenario: Revoke invitation fails if it is complete
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a role of name "dog-food" with uuid "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b" and permissions
    """
      ["TEST"]
    """
    And a complete invitation with uuid "4b4e2b8b-859a-45be-bfe0-ae88b58c333b" exists for user 1, email "dev@billie.dev" and role ID 1
    When I send a DELETE request to "/merchant/users/invitations/4b4e2b8b-859a-45be-bfe0-ae88b58c333b"
    Then the response status code should be 404
