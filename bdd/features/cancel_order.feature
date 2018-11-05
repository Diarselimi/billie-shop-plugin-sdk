Feature:
    In order to cancel an order
    I want to have an end point to cancel my orders
    And expect empty response

    Scenario: Successful new order cancellation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I start alfred
        When I send a POST request to "/order/CO123/cancel"
        Then print last response
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is canceled

    Scenario: Successful created order cancellation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I start alfred
        When I send a POST request to "/order/CO123/cancel"
        Then print last response
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is canceled

    Scenario: Successful shipped order cancellation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I start alfred
        And I start borscht
        When I send a POST request to "/order/CO123/cancel"
        Then print last response
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is canceled

    Scenario: Unsuccessful declined order cancellation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a declined order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a POST request to "/order/CO123/cancel"
        Then print last response
        Then the response status code should be 400
        And the JSON response should be:
        """
        {
            "code": "order_cancel_failed",
            "error": "Order #CO123 can not be cancelled"
        }
        """

    Scenario: Unsuccessful canceled order cancellation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a canceled order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a POST request to "/order/CO123/cancel"
        Then print last response
        Then the response status code should be 400
        And the JSON response should be:
        """
        {
            "code": "order_cancel_failed",
            "error": "Order #CO123 can not be cancelled"
        }
        """

    Scenario: Not existing order cancellation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        When I send a POST request to "/order/CO123/cancel"
        Then print last response
        Then the response status code should be 404
        And the JSON response should be:
        """
        {
            "code": "not_found",
            "error": "Order #CO123 not found"
        }
        """
