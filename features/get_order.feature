Feature:
    In order to create an order
    I send the order data to the endpoint
    And expect empty response

    Scenario: Successful order creation
        Given I add "Content-type" header equal to "application/json"
        And I have a customer "Test Customer" with roles "ROLE_API_USER" and api key "test"
        And I have an order "ABC1" with amounts (1000, 900, 100), duration 30 and comment "test order"
        When I send a GET request to "/order/ABC1"
        Then the JSON response should be:
        """
        {
            "external_code": "ABC1",
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
