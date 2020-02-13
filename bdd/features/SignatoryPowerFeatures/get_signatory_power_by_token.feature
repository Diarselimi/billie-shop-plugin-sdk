Feature: API endpoint for "GET /merchant/signatory-powers/:token" (ticket APIS-1466)

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Invalid token
    Given I get from companies service an HTTP 404 response from GET "/signatory-powers/WHATEVER"
    When I send a GET request to "/merchant/signatory-powers/WHATEVER"
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
    When I send a GET request to "/merchant/signatory-powers/c59ab4387937b48f30"
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
    When I send a GET request to "/merchant/signatory-powers/c59ab4387937b48f30"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """
    And the response status code should be 401


  Scenario: Successfully get a signatory power by ident. case token
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
    When I send a GET request to "/merchant/signatory-powers/c59ab4387937b48f30"
    And the response status code should be 200
    And the JSON response should be:
    """
      {
        "merchant_name": "Behat Merchant",
        "identity_verification_url": "http://localhost/kyc"
      }
    """
