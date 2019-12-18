Feature: An endpoint to retrieve merchant data

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Unsuccessful merchant retrieve
    When I send a GET request to "/private/merchant/100"
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Merchant with id 100 not found","code":"resource_not_found"}]}
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
       "payment_merchant_id":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
       "is_active":true,
       "webhook_url":null,
       "webhook_authorization":null,
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
       "payment_merchant_id":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
       "is_active":true,
       "webhook_url":null,
       "webhook_authorization":null,
       "credentials": null
    }
    """
