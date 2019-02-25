Feature:
    In order to retrieve the order details
    I want to call the get order endpoint

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I start alfred
        And I start borscht

    Scenario: Unsuccessful order retrieve
        Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a GET request to "/order/ABC"
        Then the response status code should be 404
        And print last JSON response
        And the JSON response should be:
        """
        {
            "code": "not_found",
            "error": "Order #ABC not found"
        }
        """

    Scenario: Successful order retrieve
        Given I get from alfred "/debtor/2" endpoint response with status 200 and body
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
            "is_blacklisted": 0,
            "payment_id": 1
        }
        """
        And I get from borscht "/debtor/1.json" endpoint response with status 200 and body
        """
        {
            "iban": "DE1234",
            "bic": "BICISHERE"
        }
        """
        And I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
        When I send a GET request to "/order/XF43Y"
        Then print last response
        Then the JSON response should be:
        """
        {
            "external_code": "XF43Y",
            "state": "new",
            "reasons": [],
            "amount": 1000,
            "debtor_company": {
                "name": "Test User Company",
                "house_number": "10",
                "street": "Heinrich-Heine-Platz",
                "postal_code": "10179",
                "city": "Berlin",
                "country": "DE"
            },
            "bank_account": {
                "iban": "DE1234",
                "bic": "BICISHERE"
            },
            "invoice": {
                "number": null,
                "payout_amount": null,
                "fee_amount": null,
                "fee_rate": null,
                "due_date": null
            },
            "debtor_external_data": {
                "name": "test",
                "address_country": "TE",
                "address_postal_code": "test",
                "address_street": "test",
                "address_house": "test",
                "industry_sector": "test"
            }
        }
        """
