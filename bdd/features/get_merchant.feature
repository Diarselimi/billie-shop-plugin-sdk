Feature: An endpoint to retrieve merchant data

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Unsuccessful merchant retrieve
    When I send a GET request to "/private/merchant/100"
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Merchant with id or uuid 100 not found","code":"resource_not_found"}]}
    """

  Scenario: Successful merchant retrieve
    Given I get from Oauth service a valid credentials response
    When I send a GET request to "/private/merchant/1"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
       "id":1,
       "name":"Behat Merchant",
       "financing_power":10000,
       "financing_limit":10000,
       "api_key":"test",
       "company_id":"10",
       "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
       "payment_merchant_id":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
       "is_active":true,
       "webhook_url":null,
       "webhook_authorization":null,
       "investor_uuid":"a5cf2662-35a4-11e9-a2c4-02c6850949d6",
       "credentials": {
          "client_id":"1234-1244-4122-asd123",
          "secret":"21ergfhgferetr3425tregdf"
       }
    }
    """

  Scenario: Successful merchant retrieve without credentials
    Given I get from Oauth service a not valid credentials response
    When I send a GET request to "/private/merchant/1"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
       "id":1,
       "name":"Behat Merchant",
       "financing_power":10000,
       "financing_limit":10000,
       "api_key":"test",
       "company_id":"10",
       "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
       "payment_merchant_id":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
       "is_active":true,
       "webhook_url":null,
       "webhook_authorization":null,
       "credentials": null,
       "investor_uuid":"a5cf2662-35a4-11e9-a2c4-02c6850949d6"
    }
    """
