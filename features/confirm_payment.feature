Feature:
    In order to cancel an order
    I want to have an end point to cancel my orders
    And expect empty response

    Scenario: Successful payment confirmation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I start borscht
        When I send a POST request to "/order/CO123/confirm-payment" with body:
        """
        {
          "amount": 1000
        }
        """
        Then print last response
        Then the response status code should be 204
        And the response should be empty

    Scenario: Unsuccessful payment confirmation
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I start borscht
        When I send a POST request to "/order/CO123/confirm-payment" with body:
        """
        {
          "amount": 1000
        }
        """
        Then print last response
        Then the response status code should be 403
        Then the JSON response should be:
        """
        {
            "code": "order_payment_confirmation_failed",
            "message": "Order #CO123 payment can not be confirmed"
        }
        """
