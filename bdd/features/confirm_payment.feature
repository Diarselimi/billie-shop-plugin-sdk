Feature:
    In order to cancel an order
    I want to have an end point to cancel my orders
    And expect empty response

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-Key" header equal to test

    Scenario: Successful payment confirmation
        Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a POST request to "/order/CO123/confirm-payment" with body:
        """
        {
          "amount": 1000
        }
        """
        Then the response status code should be 204
        And the response should be empty

    Scenario: Unsuccessful payment confirmation
        Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a POST request to "/order/CO123/confirm-payment" with body:
        """
        {
          "amount": 1000
        }
        """
        Then the response status code should be 403
        Then the JSON response should be:
        """
        {
            "code": "order_payment_confirmation_failed",
            "error": "Order #CO123 payment can not be confirmed"
        }
        """

    Scenario: Confirm payment of fraud order
        Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And The order "CO123" was already marked as fraud
        When I send a POST request to "/order/CO123/confirm-payment" with body:
        """
        {
          "amount": 1000
        }
        """
        Then the response status code should be 403
        And the JSON response should be:
        """
        {
            "error": "Order was marked as fraud"
        }
        """
