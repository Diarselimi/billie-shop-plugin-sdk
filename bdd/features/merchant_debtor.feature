Feature:
    In order to retrieve the merchant debtor details
    I call to get merchant debtor endpoint

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-Key" header equal to test

    Scenario: Update merchant debtor company
        And I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from companies service update debtor positive response
        When I send a PUT request to "/merchant/1/merchant-debtor/ext_id/company" with body:
        """
        {
            "name": "Billie1",
            "address_house": "222",
            "address_street": "Billiestr.",
            "address_postal_code": "10887",
            "address_city": "BilCity"
        }
        """
        Then the response status code should be 204

    Scenario: Update merchant debtor limit
        And I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from payments service get debtor response
        When I send a PUT request to "/merchant/1/merchant-debtor/ext_id/limit" with body:
        """
        {
            "limit": "500"
        }
        """
        Then the response status code should be 204

    Scenario: Get merchant debtor
        And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
        And I get from payments service get debtor response
        And I get from companies service get debtor response
        When I send a GET request to "/merchant/1/merchant-debtor/ext_id"
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
            "id":1,
            "company_id":"1",
            "payment_id":"test",
            "external_id":"ext_id",
            "available_limit":1000,
            "total_limit":2300,
            "created_amount":800,
            "outstanding_amount":500,
            "company":
            {
                "crefo_id":"123",
                "schufa_id":"123",
                "name":"Test User Company",
                "address_house":"10",
                "address_street":"Heinrich-Heine-Platz",
                "address_city":"Berlin",
                "address_postal_code":"10179",
                "address_country":"DE"
            }
        }
        """
