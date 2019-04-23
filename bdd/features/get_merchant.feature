Feature:
    In order to authorize within the Paella Core
    I want to call the get merchant endpoint
    and retrieve the merchant data

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-Key" header equal to test

    Scenario: Unsuccessful merchant retrieve
        When I send a GET request to "/merchant/1"
        Then the response status code should be 404
        And the JSON response should be:
        """
        {
            "code": "not_found",
            "error": "Merchant with api-key 1 not found"
        }
        """

    Scenario: Successful merchant retrieve
        When I send a GET request to "/merchant/test"
        Then the JSON response should include:
        """
        {
            "id": "1",
            "name": "Behat User",
            "available_financing_limit": 10000,
            "api_key": "test",
            "company_id": "10",
            "payment_merchant_id": "f2ec4d5e-79f4-40d6-b411-31174b6519ac",
            "roles": ["ROLE_NOTHING"],
            "is_active": true,
            "webhook_url": null,
            "webhook_authorization": null
        }
        """
