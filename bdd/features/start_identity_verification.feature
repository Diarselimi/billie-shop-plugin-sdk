Feature: API endpoint for "POST /merchant/user/start-identity-verification" (ticket APIS-1756)

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a POST request to "/merchant/user/start-identity-verification"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """
    And the response status code should be 403

  Scenario: Authenticated user without MANAGE_ONBOARDING permission fails to call POST /merchant/user/start-identity-verification
    Given a merchant user exists with permission FOOBAR
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a POST request to "/merchant/user/start-identity-verification"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """
    And the response status code should be 403

  Scenario: Failed call with missing data
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/user/start-identity-verification" with body:
    """
     {}
    """
    Then the JSON response should be:
    """
      {
        "errors": [
          {
            "title": "This value should not be blank.",
            "code": "request_validation_error",
            "source": "redirect_url_coupon_requested"
          },
          {
            "title": "This value should not be blank.",
            "code": "request_validation_error",
            "source": "redirect_url_review_pending"
          },
          {
            "title": "This value should not be blank.",
            "code": "request_validation_error",
            "source": "redirect_url_declined"
          }
        ]
      }
    """
    And the response status code should be 400

  Scenario: Failed call with wrong data
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/user/start-identity-verification" with body:
    """
     {
        "redirect_url_coupon_requested": "..zzz..",
        "redirect_url_review_pending": "..zzz..",
        "redirect_url_declined": "..zzz.."
     }
    """
    Then the JSON response should be:
    """
      {
        "errors": [
          {
            "title": "This value is not a valid URL.",
            "code": "request_validation_error",
            "source": "redirect_url_coupon_requested"
          },
          {
            "title": "This value is not a valid URL.",
            "code": "request_validation_error",
            "source": "redirect_url_review_pending"
          },
          {
            "title": "This value is not a valid URL.",
            "code": "request_validation_error",
            "source": "redirect_url_declined"
          }
        ]
      }
    """
    And the response status code should be 400

  Scenario: Failed call when webapp call fails
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    And I get from webapp API an HTTP 500 call to POST "/sdk/identity-verification" with body:
    """
     {"error":"whatever"}
    """
    When I send a POST request to "/merchant/user/start-identity-verification" with body:
    """
     {
        "redirect_url_coupon_requested": "http://localhost/a",
        "redirect_url_review_pending": "http://localhost/b",
        "redirect_url_declined": "http://localhost/c"
     }
    """
    Then the response status code should be 500

  Scenario: Failed call when webapp call responds with wrong format
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    And I get from webapp API an HTTP 200 call to POST "/sdk/identity-verification" with body:
    """
     {"foo":"bar"}
    """
    When I send a POST request to "/merchant/user/start-identity-verification" with body:
    """
     {
        "redirect_url_coupon_requested": "http://localhost/a",
        "redirect_url_review_pending": "http://localhost/b",
        "redirect_url_declined": "http://localhost/c"
     }
    """
    Then the response status code should be 500

  Scenario: Failed call when step is not new
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And The following onboarding steps are in states for merchant "f2ec4d5e-79f4-40d6-b411-31174b6519ac":
      | name                   | state     |
      | identity_verification  |  confirmation_pending |
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/user/start-identity-verification" with body:
    """
     {
        "redirect_url_coupon_requested": "http://localhost/a",
        "redirect_url_review_pending": "http://localhost/b",
        "redirect_url_declined": "http://localhost/c"
     }
    """
    Then the JSON response should be:
    """
      {"errors":[{"title":"Merchant Onboarding Step transition is not possible.","code":"forbidden"}]}
    """
    And the response status code should be 403

  Scenario: Successful call
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    And I get from webapp API an HTTP 200 call to POST "/sdk/identity-verification" with body:
    """
     {"data":{"uuid":"a9062fa9-053e-455f-9908-f92bffe48b65", "url":"http://localhost/postident"}}
    """
    When I send a POST request to "/merchant/user/start-identity-verification" with body:
    """
     {
        "redirect_url_coupon_requested": "http://localhost/a",
        "redirect_url_review_pending": "http://localhost/b",
        "redirect_url_declined": "http://localhost/c"
     }
    """
    Then the JSON response should be:
    """
      {"url":"http://localhost/postident"}
    """
    And the response status code should be 200
