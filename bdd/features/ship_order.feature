Feature:
    In order to ship an order
    I want to have an end point to ship my orders
    And expect empty response

    Scenario: Successful order shipment
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I start borscht
        And I get from borscht "/order.json" endpoint response with status 200 and body
        """
        {
            "id": 1,
            "state": "new",
            "payout_amount": 1000,
            "outstanding_amount": 1000,
            "fee_amount": 10,
            "fee_rate": 1,
            "due_date": "20-11-1978"
        }
        """
        And I start alfred
        And I get from alfred "/debtor/1" endpoint response with status 200 and body
        """
        {
            "id": 1,
            "payment_id": "test",
            "name": "Test User Company",
            "address_house": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_city": "Berlin",
            "address_postal_code": "10179",
            "address_country": "DE",
            "address_addition": null,
            "crefo_id": "123",
            "schufa_id": "123",
            "is_blacklisted": 0
        }
        """
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
