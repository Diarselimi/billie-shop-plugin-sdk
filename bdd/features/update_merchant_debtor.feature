Feature:
    In order to update the merchant debtor details
    I call the update merchant debtor endpoint

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
