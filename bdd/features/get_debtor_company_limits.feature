Feature:
  In order to retrieve the merchant debtor details
  I call the get merchant debtor endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Get merchant debtor details
    And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
    And I get from payments service get debtor response
    And I get from companies service get debtor response
    When I send a GET request to "/private/debtor-company/c7be46c0-e049-4312-b274-258ec5aeeb70/limits"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "company_id": 1,
      "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
      "company_financing_power": 1000,
      "merchant_debtors": [
        {
          "merchant_id": "1",
          "merchant_debtor_id": 1,
          "merchant_debtor_uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
          "financing_limit": 2000,
          "financing_power": 1000
        }
      ]
    }
    """

  Scenario: Get merchant debtor details - not found error
    And I get from companies service "/debtor/944c4cf4-3eb8-4ff3-872e-961deac79702" endpoint response with status 404 and body
    """
    {
      "error": "Debtor does not exist.",
      "code": 404
    }
    """
    When I send a GET request to "/private/debtor-company/944c4cf4-3eb8-4ff3-872e-961deac79702/limits"
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Company not found.","code":"resource_not_found"}]}
    """

  Scenario: Get merchant debtor details - invalid uuid error
    When I send a GET request to "/private/debtor-company/944c4cf4-3eb8-4ff3-872e-xxxx/limits"
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
      "errors": [
        {
          "source": "uuid",
          "title": "This is not a valid UUID.",
          "code": "request_validation_error"
        }
      ]
    }
    """
