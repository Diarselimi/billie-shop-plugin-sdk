Feature:
    In order to authorize within the Paella Core
    I want to call the get customer endpoint
    and retrieve the customer data

    Scenario: Unsuccessful customer retrieve
        Given I add "Content-type" header equal to "application/json"
        And I have a customer "Test Customer" with roles "ROLE_API_USER" and api key "UIO543X"
        When I send a GET request to "/customer/1"
        Then the response status code should be 404
        And the JSON response should be:
        """
        {
            "code":400001,
            "message":"Customer with api-key 1 not found"
        }
        """

    Scenario: Successful customer retrieve
        Given I add "Content-type" header equal to "application/json"
        And I have a customer "Test Customer" with roles "ROLE_API_USER" and api key "UIO543X"
        When I send a GET request to "/customer/UIO543X"
        Then the JSON response should be:
        """
        {
            "name": "Test Customer",
            "api_key": "UIO543X",
            "roles": "ROLE_API_USER",
            "is_active": "1",
            "available_financing_limit": "1000"
        }
        """
