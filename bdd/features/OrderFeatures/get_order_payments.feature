Feature:
  In order to retrieve the order payments
  I want to call the get order payments endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_ORDERS

  Scenario: Successful order payments retrieve
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a GET request to "/order/test-order-uuidXF43Y/payments"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "items": [
        {
          "created_at": "2018-06-28T17:10:05",
          "amount": 67.12,
          "type": "invoice_payback",
          "state": "complete"
        }
      ],
      "total": 1
    }
    """
