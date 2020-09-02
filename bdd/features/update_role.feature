Feature: Update role

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Update user role happy path
    Given a merchant user exists with role admin and permission MANAGE_USERS
    And a merchant user exists with uuid "fed64fa2-d591-43ae-b3b9-5f758dcc57ae" and role developer and permission CREATE_ORDERS
    And a role support exists with uuid "299f98ef-cb67-4aab-9cc8-c86b7059073d" and permission VIEW_ORDERS
    And an invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" for user uuid "fed64fa2-d591-43ae-b3b9-5f758dcc57ae" exists for role uuid "299f98ef-cb67-4aab-9cc8-c86b7059073d"
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/user/role" with body:
    """
    {
      "user_uuid": "fed64fa2-d591-43ae-b3b9-5f758dcc57ae",
      "role_uuid": "299f98ef-cb67-4aab-9cc8-c86b7059073d"
    }
    """
    Then the response status code should be 204
    And the user with uuid "fed64fa2-d591-43ae-b3b9-5f758dcc57ae" has role support
    And the invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" has role support

  Scenario: Update invitation role happy path
    Given a merchant user exists with role admin and permission MANAGE_USERS
    And a role developer exists with uuid "e733a971-9ad0-4a69-8d70-6a06ec05ecee" and permission VIEW_ORDERS
    # role id 2 is the role we just created
    And an invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" and status "pending" exists for email "dev@billie.dev" and role ID 2
    And a role support exists with uuid "299f98ef-cb67-4aab-9cc8-c86b7059073d" and permission VIEW_ORDERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/users/invitations/role" with body:
    """
    {
      "email": "dev@billie.dev",
      "role_uuid": "299f98ef-cb67-4aab-9cc8-c86b7059073d"
    }
    """
    Then the response status code should be 204
    And the invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" has role support
