Feature:
    In order to authorize within the Paella Core
    I want to call the get merchant endpoint
    and retrieve the merchant data

    Scenario: Unsuccessful merchant retrieve
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        When I send a GET request to "/merchant/1"
        Then the response status code should be 404
        And print last JSON response
        And the JSON response should be:
        """
        {
            "code": "not_found",
            "error": "Merchant with api-key 1 not found"
        }
        """

    Scenario: Successful merchant retrieve
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to test
        When I send a GET request to "/merchant/test"
        And print last JSON response
        Then the JSON response should include:
        """
        {
            "id": "1",
            "name": "Behat User",
            "api_key": "test",
            "company_id": "1",
            "payment_merchant_id": "f2ec4d5e-79f4-40d6-b411-31174b6519ac",
            "roles": "[\u0022ROLE_NOTHING\u0022]",
            "is_active": "1",
            "available_financing_limit": "10000.00",
            "webhook_url": null,
            "webhook_authorization": null
        }
        """
