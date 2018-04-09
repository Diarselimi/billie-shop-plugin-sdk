Feature:
    In order to retrieve the order details
    I want to call the get order endpoint

    Scenario: Unsuccessful order retrieve
        Given I add "Content-type" header equal to "application/json"
        And I have a customer "Test Customer" with roles "ROLE_API_USER" and api key "test"
        And I have an order "XF43Y" with amounts (1000, 900, 100), duration 30 and comment "test order"
        When I send a GET request to "/order/ABC"
        Then the response status code should be 404
        And print last JSON response
        And the JSON response should be:
        """
        {
            "code":400001,
            "message":"Order #ABC not found"
        }
        """

    Scenario: Successful order retrieve
        Given I add "Content-type" header equal to "application/json"
        And I have a customer "Test Customer" with roles "ROLE_API_USER" and api key "test"
        And I have an order "XF43Y" with amounts (1000, 900, 100), duration 30 and comment "test order"
        When I send a GET request to "/order/XF43Y"
        Then the JSON response should be:
        """
        {
            "external_code": "XF43Y",
            "state": "new",
            "debtor_company": {
                "name": null,
                "house_number": null,
                "street": null,
                "postal_code": null,
                "country": null
            },
            "bank_account": {
                "iban": null,
                "bic": null
            },
            "invoice": {
                "number": null,
                "payout_amount": null,
                "fee_amount": null,
                "fee_rate": null,
                "due_date": null
            }
        }
        """
