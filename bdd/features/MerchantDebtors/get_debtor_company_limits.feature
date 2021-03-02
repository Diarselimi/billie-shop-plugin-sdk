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
    And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    When I send a GET request to "/private/debtor-company/c7be46c0-e049-4312-b274-258ec5aeeb70/limits"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "company_id": 1,
      "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
      "company_financing_power": 22000,
      "merchant_debtors": [
        {
          "id": 1,
          "uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
          "financing_limit": 7500,
          "financing_power": 4500,
          "merchant": {
            "id": 1,
            "payment_uuid": "f2ec4d5e-79f4-40d6-b411-31174b6519ac"
          }
        }
      ]
    }
    """

  Scenario: Get merchant debtor details while he has no orders created yet.
    Given I have default limits and no order created yet
    And I get from payments service get debtor response
    And I get from companies service get debtor response
    And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    When I send a GET request to "/private/debtor-company/c7be46c0-e049-4312-b274-258ec5aeeb70/limits"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "company_id": 1,
        "company_financing_power": 22000,
        "merchant_debtors": [
            {
                "merchant": {
                    "id": 1,
                    "payment_uuid": "f2ec4d5e-79f4-40d6-b411-31174b6519ac"
                },
                "uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
                "financing_limit": 7500,
                "id": 1,
                "financing_power": 4500
            }
        ],
        "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70"
    }
    """

  Scenario: Company with no merchant debtors
    And I get from payments service get debtor response
    And I get from companies service get debtor response
    And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    When I send a GET request to "/private/debtor-company/c7be46c0-e049-4312-b274-258ec5aeeb70/limits"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "company_id": 1,
      "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
      "company_financing_power": 22000,
      "merchant_debtors": []
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
