Feature:
    In order to update an order
    I want to have an end point to update my orders
    And expect empty response

    Scenario: Order exists, not yet shipped, due date provided, amount unchanged
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
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" duration is 30
        And the order "CO123" amountGross is 1000

    Scenario: Order exists, not yet shipped, due date unchanged/not set, valid new amount
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

    Scenario: Order exists, is shipped but not paid back, valid new due date*, amount unchanged
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

    Scenario: Order exists, is shipped but not paid back, due date unchanged, new valid amount
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

    Scenario: Order exists, is shipped but not paid back, new due date invalid
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
        Then the response status code should be 400

    Scenario: Order exists, is shipped but not paid back, new amount invalid
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
        Then the response status code should be 400

    Scenario: Order exists, is shipped but not paid back, valid new due date, valid new amount
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
