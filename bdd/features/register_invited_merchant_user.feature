Feature: Register merchant user to access dashboard

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Request with non-existing token fails
    When I send a POST request to "/public/merchant/users/invitations/4b4e2b8b859a45bebfe0ae88b58c333b/signup"
    Then the response status code should be 401
    And the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: Request for an already complete invitation
    Given I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    And an invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" and status "complete" exists for email "dev@billie.dev" and role ID 1
    When I send a POST request to "/public/merchant/users/invitations/4b4e2b8b859a45bebfe0ae88b58c333b/signup" with body:
    """
    {
        "first_name": "Cool",
        "last_name": "Alex",
        "password": "this.is.the.end.1234"
    }
    """
    Then the response status code should be 401
    And the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: Request with a weak password
    Given I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    And an invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" and status "pending" exists for email "dev@billie.dev" and role ID 1
    When I send a POST request to "/public/merchant/users/invitations/4b4e2b8b859a45bebfe0ae88b58c333b/signup" with body:
    """
    {
        "first_name": "Cool",
        "last_name": "Alex",
        "password": "d"
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {"errors":[{"title":"The password should be at least 8 characters long and contain at least one letter and one digit","code":"request_validation_error","source":"password"}]}
    """

  Scenario: Request for an existing user email
    Given I will get a response from Authentication Service from endpoint "users" with status code "409"
    And I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    And an invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" and status "pending" exists for email "dev@billie.dev" and role ID 1
    When I send a POST request to "/public/merchant/users/invitations/4b4e2b8b859a45bebfe0ae88b58c333b/signup" with body:
    """
    {
        "first_name": "Cool",
        "last_name": "Alex",
        "password": "this.is.the.end.1234"
    }
    """
    Then the response status code should be 409
    And the JSON response should be:
    """
      {"errors":[{"title":"Merchant user with the same login already exists","code":"resource_already_exists"}]}
    """

  Scenario: Successfully register merchant user via invitation
    Given I successfully create OAuth client with email "dev@billie.dev" and user id "oauthUserId"
    And I successfully obtain token from oauth service
    And I get from companies service update debtor positive response
    And I get from Oauth service a valid user token
    And I have a role of name "Test" with uuid "c7be46c0-e049-4312-b274-258ec5aeeb70" and permissions
    """
      ["TEST"]
    """
    And an invitation with token "4b4e2b8b859a45bebfe0ae88b58c333b" and status "pending" exists for email "dev@billie.dev" and role ID 1
    When I send a POST request to "/public/merchant/users/invitations/4b4e2b8b859a45bebfe0ae88b58c333b/signup" with body:
    """
    {
        "first_name": "Cool",
        "last_name": "Alex",
        "password": "this.is.the.end.1234"
    }
    """
    Then the JSON response should be:
    """
      {
        "access_token": "testToken",
        "user": {
            "uuid": "oauthUserId",
            "first_name": "Cool",
            "last_name": "Alex",
            "email":"dev@billie.dev",
            "role": {
              "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
              "name": "Test"
            },
            "permissions": [
                "TEST"
            ],
            "merchant_company": {
                "name": "Test User Company",
                "address_street": "Heinrich-Heine-Platz",
                "address_postal_code": "10179",
                "address_country": "DE",
                "address_house_number": "10",
                "address_city": "Berlin"
            },
            "tracking_id": 1,
            "onboarding_state": "new"
        }
      }
    """
    And the response status code should be 200
    And merchant user with merchant id 1 and user id "oauthUserId" should be created
