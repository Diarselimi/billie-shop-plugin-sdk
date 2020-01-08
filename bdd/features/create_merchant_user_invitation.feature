Feature: APIS-1443 - Invite user by email and role

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a POST request to "/merchant/users/invitations"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Authenticated user without MANAGE_USERS permission cannot invite users
    Given a merchant user exists with permission VIEW_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a POST request to "/merchant/users/invitations"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Cannot create invitation with missing data
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a POST request to "/merchant/users/invitations" with body:
    """
      {}
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
        "errors":[
          {"title":"This value should not be blank.","code":"request_validation_error","source":"email"},
          {"title":"This value should not be blank.","code":"request_validation_error","source":"role_uuid"}
        ]
      }
    """

  Scenario: Cannot create invitation with invalid data
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a POST request to "/merchant/users/invitations" with body:
    """
      {"email": "invalid_email", "role_uuid": "invalid"}
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
        "errors":[
          {"title":"This value is not a valid email address.","code":"request_validation_error","source":"email"},
          {"title":"This is not a valid UUID.","code":"request_validation_error","source":"role_uuid"}
        ]
      }
    """

  Scenario: Successfully invite user
    Given a merchant user exists with permission MANAGE_USERS
    And I have a role of name "human-cookie" with uuid "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b" and permissions
    """
      ["TEST"]
    """
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a POST request to "/merchant/users/invitations" with body:
    """
      {"email": "dev@billie.dev", "role_uuid": "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b"}
    """
    Then the response status code should be 200
    And the JSON response should have "invitation_uuid"

  Scenario: Create invitation succeeds if already exists for same email but is not valid anymore
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a role of name "human-cookie" with uuid "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b" and permissions
    """
      ["TEST"]
    """
    And an invitation exists for email "dev@billie.dev", role ID 1 and "expired" invitation
    When I send a POST request to "/merchant/users/invitations" with body:
    """
      {"email": "dev@billie.dev", "role_uuid": "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b"}
    """
    Then the response status code should be 200
    And the JSON response should have "invitation_uuid"


  Scenario: Create invitation fails if already exists for same email and is still valid
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a role of name "human-cookie" with uuid "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b" and permissions
    """
      ["TEST"]
    """
    And an invitation exists for email "dev@billie.dev", role ID 1 and "pending" invitation
    When I send a POST request to "/merchant/users/invitations" with body:
    """
      {"email": "dev@billie.dev", "role_uuid": "d57dcd58-6f88-42fc-8f14-6b8c4af8a29b"}
    """
    Then the response status code should be 409


  Scenario: Create invitation fails if role is not found
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a POST request to "/merchant/users/invitations" with body:
    """
      {"email": "dev+2@billie.dev", "role_uuid": "894139ec-c687-4ac6-aefb-5ef7cdb31f66"}
    """
    Then the response status code should be 404


  Scenario Template: Create invitation fails if role is blacklisted
    Given a merchant user exists with permission MANAGE_USERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a role of name "<role>" with uuid "<uuid>" and permissions
    """
      ["TEST"]
    """
    Examples:
      | role         | uuid                                 | httpStatus |
      | none         | d57dcd58-6f88-42fc-8f14-6b8c4af8a29b | 404        |
      | admin        | 4bf36bc7-5d71-457b-9734-b2bdf2f1eb88 | 404        |
      | billie_admin | 7bc57357-1cde-425f-b5ca-8e42d16a88e1 | 404        |
      | foo          | 9408d130-d9cc-43e2-b198-f3d82fbc3d9c | 200        |
    When I send a POST request to "/merchant/users/invitations" with body:
    """
      {"email": "dev@billie.dev", "role_uuid": "<uuid>"}
    """
    Then the response status code should be "<httpStatus>"


