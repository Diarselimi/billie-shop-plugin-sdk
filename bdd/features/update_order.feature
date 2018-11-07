Feature:
  In order to update an order
  I want to have an end point to update my orders
  And expect empty response

  Scenario: Case 1: Order exists, not yet shipped, due date provided, amount unchanged
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 50,
          "amount_gross": 1000,
          "amount_net": 900,
          "amount_tax": 100
        }
        """
    Then print last response
    Then the response status code should be 412
    And the JSON response should be:
    """
    {"code":"order_duration_update_not_possible","error":"Update duration not possible"}
    """

  Scenario: Case 1.1: Order exists, not yet shipped, due date provided, valid new amount
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred
    And I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "amount_gross": 500,
          "amount_net": 400,
          "amount_tax": 20
        }
        """
    Then print last response
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 30
    And the order "CO123" amountGross is 500

  Scenario: Case 2: Order exists, not yet shipped, due date unchanged/not set, valid new amount
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred
    And I start borscht
    And I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "amount_gross": 500,
          "amount_net": 400,
          "amount_tax": 20
        }
        """
    Then print last response
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 30
    And the order "CO123" amountGross is 500
    And the order "CO123" amountNet is 400
    And the order "CO123" amountTax is 20

  Scenario: Case 3: Order exists, is shipped but not paid back, valid new due date*, amount unchanged
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred
    And I start borscht
    And I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 50,
          "amount_gross": 1000,
          "amount_net": 900,
          "amount_tax": 100
        }
        """
    Then print last response
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 50
    And the order "CO123" amountGross is 1000

  Scenario: Case 4: Order exists, is shipped but not paid back, due date unchanged, new valid amount
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred
    And I start borscht
    And I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 30,
          "amount_gross": 500,
          "amount_net": 400,
          "amount_tax": 20
        }
        """
    Then print last response
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 30
    And the order "CO123" amountGross is 500
    And the order "CO123" amountNet is 400
    And the order "CO123" amountTax is 20

  Scenario: Case 5: Order exists, is shipped but not paid back, new duration invalid
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred
    And I start borscht
    And I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 20
        }
        """
    Then print last response
    Then the response status code should be 412
    And the JSON response should be:
    """
    {"code":"order_validation_failed","error":"Invalid duration"}
    """

  Scenario: Case 6: Order exists, is shipped but not paid back, new amount invalid
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred
    And I start borscht
    And I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "amount_gross": 2000,
          "amount_net": 1800,
          "amount_tax": 200
        }
        """
    Then print last response
    Then the response status code should be 412
    And the JSON response should be:
    """
    {"code":"order_validation_failed","error":"Invalid amount"}
    """

  Scenario: Case 7: Order exists, is shipped but not paid back, valid new due date, valid new amount
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred
    And I start borscht
    And I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 50,
          "amount_net": 400,
          "amount_gross": 500,
          "amount_tax": 20
        }
        """
    Then print last response
    Then the response status code should be 204
    And the order "CO123" duration is 50
    And the order "CO123" amountGross is 500
    And the order "CO123" amountNet is 400
    And the order "CO123" amountTax is 20

  Scenario: Case 8: Order does not exist
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    When I send a PATCH request to "/order/CO123" with body:
    """
    {
        "duration": 50,
        "amount_net": 400,
        "amount_gross": 500,
        "amount_tax": 20
    }
    """
    Then print last response
    Then the response status code should be 404

  Scenario: Case 9: Order was marked as fraud
    Given I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "CO123" was already marked as fraud
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 30,
          "amount_gross": 500,
          "amount_net": 400,
          "amount_tax": 20
        }
        """
    Then the response status code should be 403
    And print last JSON response
    And the JSON response should be:
        """
        {
            "error": "Order was marked as fraud"
        }
        """
