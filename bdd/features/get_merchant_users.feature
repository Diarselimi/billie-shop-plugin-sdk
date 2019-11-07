Feature: Get current logged in merchant user details

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a GET request to "/merchant/users"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Authenticated user without VIEW_USERS permission cannot get users list
    Given a merchant user exists with permission FOO_BAR
    And I get from Oauth service a valid user token
	And I add "Authorization" header equal to "Bearer someToken"
    When I send a GET request to "/merchant/users"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Successfully retrieve merchant users list
    Given a merchant user exists with permission VIEW_USERS
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I get from OAuth client the following list of users:
    """
    [
      {"user_id":"oauthUserId", "user_email": "test+smaug@billie.dev"},
      {"user_id":"oauthUserId-2", "user_email": "test2+smaug@billie.dev"}
    ]
    """
    And an invitation exists for email "test+3@billie.dev", role ID 1 and "expired" invitation
    And an invitation exists for email "test+4@billie.dev", role ID 1 and "pending" invitation
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a GET request to "/public/merchant/users"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "total": 3,
      "items": [
        {
          "uuid": null,
          "first_name": null,
          "last_name": null,
          "email": "test+3@billie.dev",
          "role": {
            "uuid": "test_uuid",
            "name": "test"
          },
          "invitation_uuid": "test_uuid-test+3@billie.dev",
          "invitation_status": "expired"
        },
        {
          "uuid": null,
          "first_name": null,
          "last_name": null,
          "email": "test+4@billie.dev",
          "role": {
            "uuid": "test_uuid",
            "name": "test"
          },
          "invitation_uuid": "test_uuid-test+4@billie.dev",
          "invitation_status": "pending"
        },
        {
          "uuid": "oauthUserId",
          "first_name": "test",
          "last_name": "test",
          "email": "test@billie.dev",
          "role": {
            "uuid": "test_uuid",
            "name": "test"
          },
          "invitation_uuid": null,
          "invitation_status": "complete"
        }
      ]
    }
    """
