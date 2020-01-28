Feature: API endpoint for "POST /merchant/signatory-powers/{token}/start-identity-verification" (ticket APIS-1466)

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Invalid token
    Given I get from companies service an HTTP 404 response from GET "/signatory-powers/WHATEVER"
    When I send a POST request to "/merchant/signatory-powers/WHATEVER/start-identity-verification"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """
    And the response status code should be 401

  Scenario: Signatory Power is already identified
    Given I get from companies service an HTTP 200 response from GET "/signatory-powers/c59ab4387937b48f30" with body:
    """
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Billie",
        "last_name": "Jean",
        "email": "dev@billie.dev",
        "company_uuid": "dff7d824-a65f-4278-99d1-7041875bd899",
        "identity_verification_url": "http://localhost/kyc",
        "is_identity_verified": true
      }
    """
    When I send a POST request to "/merchant/signatory-powers/c59ab4387937b48f30/start-identity-verification"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """
    And the response status code should be 401


  Scenario: Associated merchant company uuid does not exist
    Given I get from companies service an HTTP 200 response from GET "/signatory-powers/c59ab4387937b48f30" with body:
    """
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Billie",
        "last_name": "Jean",
        "email": "dev@billie.dev",
        "company_uuid": "dff7d824-a65f-4278-99d1-7041875bd899",
        "identity_verification_url": "http://localhost/kyc",
        "is_identity_verified": false
      }
    """
    When I send a POST request to "/merchant/signatory-powers/c59ab4387937b48f30/start-identity-verification"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """
    And the response status code should be 401

  Scenario: Failed call with missing data
    Given I get from companies service an HTTP 200 response from GET "/signatory-powers/c59ab4387937b48f30" with body:
    """
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Billie",
        "last_name": "Jean",
        "email": "dev@billie.dev",
        "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "identity_verification_url": "http://localhost/kyc",
        "is_identity_verified": false
      }
    """
    When I send a POST request to "/merchant/signatory-powers/c59ab4387937b48f30/start-identity-verification" with body:
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

#####

 Scenario: Failed call with wrong data
    Given I get from companies service an HTTP 200 response from GET "/signatory-powers/c59ab4387937b48f30" with body:
    """
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Billie",
        "last_name": "Jean",
        "email": "dev@billie.dev",
        "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "identity_verification_url": "http://localhost/kyc",
        "is_identity_verified": false
      }
    """
    When I send a POST request to "/merchant/signatory-powers/c59ab4387937b48f30/start-identity-verification" with body:
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
    Given I get from companies service an HTTP 200 response from GET "/signatory-powers/c59ab4387937b48f30" with body:
    """
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Billie",
        "last_name": "Jean",
        "email": "dev@billie.dev",
        "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "identity_verification_url": "http://localhost/kyc",
        "is_identity_verified": false
      }
    """
    And I get from companies service an HTTP 204 response from POST "/signatory-powers/c7be46c0-e049-4312-b274-258ec5aeeb70/accept-tc"
    And I get from companies service an HTTP 204 response from POST "/signatory-powers/assign-identity-verification"
    And I get from webapp API an HTTP 500 call to POST "/sdk/identity-verification.json" with body:
    """
     {"error":"whatever"}
    """
    When I send a POST request to "/merchant/signatory-powers/c59ab4387937b48f30/start-identity-verification" with body:
    """
     {
        "redirect_url_coupon_requested": "http://localhost/a",
        "redirect_url_review_pending": "http://localhost/b",
        "redirect_url_declined": "http://localhost/c"
     }
    """
    Then the response status code should be 500

  Scenario: Failed call when webapp call responds with wrong format
    Given I get from companies service an HTTP 200 response from GET "/signatory-powers/c59ab4387937b48f30" with body:
    """
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Billie",
        "last_name": "Jean",
        "email": "dev@billie.dev",
        "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "identity_verification_url": "http://localhost/kyc",
        "is_identity_verified": false
      }
    """
    And I get from companies service an HTTP 204 response from POST "/signatory-powers/c7be46c0-e049-4312-b274-258ec5aeeb70/accept-tc"
    And I get from companies service an HTTP 204 response from POST "/signatory-powers/assign-identity-verification"
    And I get from webapp API an HTTP 200 call to POST "/sdk/identity-verification.json" with body:
    """
     {"foo":"bar"}
    """
    When I send a POST request to "/merchant/signatory-powers/c59ab4387937b48f30/start-identity-verification" with body:
    """
     {
        "redirect_url_coupon_requested": "http://localhost/a",
        "redirect_url_review_pending": "http://localhost/b",
        "redirect_url_declined": "http://localhost/c"
     }
    """
    Then the response status code should be 500

  Scenario: Successful call
    Given I get from companies service an HTTP 200 response from GET "/signatory-powers/c59ab4387937b48f30" with body:
    """
      {
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "first_name": "Billie",
        "last_name": "Jean",
        "email": "dev@billie.dev",
        "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
        "identity_verification_url": "http://localhost/kyc",
        "is_identity_verified": false
      }
    """
    And I get from companies service an HTTP 204 response from POST "/signatory-powers/c7be46c0-e049-4312-b274-258ec5aeeb70/accept-tc"
    And I get from companies service an HTTP 204 response from POST "/signatory-powers/assign-identity-verification"
    And I get from webapp API an HTTP 200 call to POST "/sdk/identity-verification.json" with body:
    """
     {"data":{"uuid":"a9062fa9-053e-455f-9908-f92bffe48b65", "url":"http://localhost/postident"}}
    """
    When I send a POST request to "/merchant/signatory-powers/c59ab4387937b48f30/start-identity-verification" with body:
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
