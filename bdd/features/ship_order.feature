Feature:
    In order to ship an order
    I want to have an end point to ship my orders
    And expect empty response

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1

    Scenario: Order doesn't exist
        When I send a POST request to "/order/ADDDD/ship" with body:
        """
        {
            "invoice_number": "CO123",
            "invoice_url": "http://example.com/invoice/is/here",
            "proof_of_delivery_url": "http://example.com/proove/is/here"
        }
        """
        Then the response status code should be 404
        And the JSON response should be:
        """
        {
            "code": "not_found",
            "error": "Order #ADDDD not found"
        }
        """

    Scenario: Successful order shipment
        Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from payments service create ticket response
        And I get from companies service get debtor response
        When I send a POST request to "/order/CO123/ship" with body:
        """
        {
            "invoice_number": "CO123",
            "invoice_url": "http://example.com/invoice/is/here",
            "proof_of_delivery_url": "http://example.com/proove/is/here"
        }
        """
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is in state shipped
