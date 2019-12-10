Feature: Get current logged in merchant user details

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a GET request to "/merchant/user"
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """

  Scenario: Successfully retrieve merchant user details
    Given a merchant user exists with overridden permission FOO_BAR
    And I get from companies service get debtor response
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a GET request to "/public/merchant/user"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "uuid": "oauthUserId",
        "first_name": "test",
        "last_name": "test",
        "email":"test@billie.dev",
        "merchant_company": {
            "name": "Test User Company",
            "address_street": "Heinrich-Heine-Platz",
            "address_postal_code": "10179",
            "address_country": "DE",
            "address_house_number": "10",
            "address_city": "Berlin"
        },
        "tracking_id": 1,
        "role": {"uuid": "test_uuid", "name":"test"},
        "permissions": [
            "FOO_BAR"
        ],
        "onboarding_state": "new",
        "onboarding_complete_at": null
    }
    """

  Scenario: Successfully retrieve merchant user details with overridden permissions
    Given a merchant user exists with a role with permission "TEST" and overridden permission "THIS_IS_OVERRIDDEN"
    And I get from companies service get debtor response
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a GET request to "/public/merchant/user"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "uuid": "oauthUserId",
        "first_name": "test",
        "last_name": "test",
        "email":"test@billie.dev",
        "merchant_company": {
            "name": "Test User Company",
            "address_street": "Heinrich-Heine-Platz",
            "address_postal_code": "10179",
            "address_country": "DE",
            "address_house_number": "10",
            "address_city": "Berlin"
        },
        "tracking_id": 1,
        "role": {"uuid": "test_uuid", "name":"test"},
        "permissions": [
            "THIS_IS_OVERRIDDEN"
        ],
        "onboarding_state": "new",
        "onboarding_complete_at": null
    }
    """
