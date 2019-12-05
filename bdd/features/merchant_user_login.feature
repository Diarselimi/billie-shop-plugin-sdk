Feature: Enable merchant users to login

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Invalid request - empty and invalid values
    When I send a POST request to "/public/merchant/user/login" with body:
    """
      {
        "email": "d",
        "password": ""
      }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
        "errors": [
          {
            "source": "email",
            "title": "This value is not a valid email address.",
            "code": "request_validation_error"
          },
          {
            "source": "password",
            "title": "This value should not be blank.",
            "code": "request_validation_error"
          }
        ]
      }
    """

  Scenario: Invalid request - missing fields
    When I send a POST request to "/public/merchant/user/login" with body:
    """
      {}
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
        "errors": [
          {
            "source": "email",
            "title": "This value should not be blank.",
            "code": "request_validation_error"
          },
          {
            "source": "password",
            "title": "This value should not be blank.",
            "code": "request_validation_error"
          }
        ]
      }
    """

  Scenario: Invalid credentials
    Given I get from Oauth service invalid credentials response
    When I send a POST request to "/merchant/user/login" with body:
    """
      {
        "email": "testUser@merchant.com",
        "password": "testPassword"
      }
    """
    Then the response status code should be 401

  Scenario: Valid credentials - successful login of an incomplete merchant user
    Given a merchant user exists with overridden permission VIEW_DEBTORS
    And I successfully obtain token from oauth service
    And I get from companies service update debtor positive response
    And I get from Oauth service a valid user token
    When I send a POST request to "/merchant/user/login" with body:
    """
      {
        "email": "testUser@merchant.com",
        "password": "testPassword"
      }
    """
    Then the response status code should be 200
    And the JSON response should be:
    """
      {
        "access_token": "testToken",
        "user": {
            "uuid": "oauthUserId",
            "first_name": "test",
            "last_name": "test",
            "email":"test@billie.dev",
            "role": {
              "uuid": "test_uuid",
              "name": "test"
            },
            "permissions": [
                "VIEW_DEBTORS"
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
            "onboarding_state": "new",
            "onboarding_complete_at": null
        }
      }
    """


  Scenario: Valid credentials - successful login of an complete merchant user
    Given a merchant user exists with overridden permission VIEW_DEBTORS
    And a merchant "f2ec4d5e-79f4-40d6-b411-31174b6519ac" is complete at "2018-05-16"
    And I successfully obtain token from oauth service
    And I get from companies service update debtor positive response
    And I get from Oauth service a valid user token
    When I send a POST request to "/merchant/user/login" with body:
    """
      {
        "email": "testUser@merchant.com",
        "password": "testPassword"
      }
    """
    Then the response status code should be 200
    And the JSON response should be:
    """
      {
        "access_token": "testToken",
        "user": {
            "uuid": "oauthUserId",
            "first_name": "test",
            "last_name": "test",
            "email":"test@billie.dev",
            "role": {
              "uuid": "test_uuid",
              "name": "test"
            },
            "permissions": [
                "VIEW_DEBTORS"
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
            "onboarding_state": "complete",
            "onboarding_complete_at": "2018-05-16"
        }
      }
    """
