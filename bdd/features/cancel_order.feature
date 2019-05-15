Feature:
    In order to cancel an order
    I want to have an end point to cancel my orders
    And expect empty response

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-Key" header equal to test

    Scenario: Successful new order cancellation
        Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from companies service identify match and good decision response
        And I get from companies service "/debtor/1/unlock" endpoint response with status 204 and body
        """
        """
        When I send a POST request to "/order/CO123/cancel"
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is in state canceled

    Scenario: Successful created order cancellation
        Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from companies service identify match and good decision response
        When I send a POST request to "/order/CO123/cancel"
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is in state canceled

    Scenario: Successful shipped order cancellation
        Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a POST request to "/order/CO123/cancel"
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is in state canceled

    Scenario: Unsuccessful declined order cancellation
        Given I have a declined order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a POST request to "/order/CO123/cancel"
        Then the response status code should be 400
        And the JSON response should be:
        """
        {
            "code": "order_cancel_failed",
            "error": "Order #CO123 can not be cancelled"
        }
        """

    Scenario: Unsuccessful canceled order cancellation
        Given I have a canceled order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a POST request to "/order/CO123/cancel"
        Then the response status code should be 400
        And the JSON response should be:
        """
        {
            "code": "order_cancel_failed",
            "error": "Order #CO123 can not be cancelled"
        }
        """

    Scenario: Not existing order cancellation
        When I send a POST request to "/order/CO123/cancel"
        Then the response status code should be 404
        And the JSON response should be:
        """
        {
            "code": "not_found",
            "error": "Order #CO123 not found"
        }
        """

    Scenario: Fraud order cancellation
        Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And The order "CO123" was already marked as fraud
        When I send a POST request to "/order/CO123/cancel"
        Then the response status code should be 403
        And the JSON response should be:
        """
        {
            "error": "Order was marked as fraud"
        }
        """
