Feature:
    In order to retrieve the merchant debtor list
    I call the get merchant debtors endpoint

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "Authorization" header equal to "Bearer someToken"
        And I get from Oauth service a valid user token
        And a merchant user exists with permission VIEW_DEBTORS

    Scenario: Get merchant debtors overview
        And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
        And I get from payments service get debtor response
        And I get from companies service get debtor response
        And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
        When I send a GET request to "/public/debtors"
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
            "total": 1,
            "items": [
                {
                    "id":"ad74bbc4-509e-47d5-9b50-a0320ce3d715",
                    "external_code":"ext_id",
                    "name":"Test User Company",
                    "financing_limit":7500,
                    "financing_power":4500,
                    "bank_account_iban":"DE1234",
                    "bank_account_bic":"BICISHERE",
                    "created_at":"2019-01-01T12:00:00+0100"
                }
            ]
        }
        """

    Scenario: Get merchant debtors overview, filtering by code
        And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
        And I get from payments service get debtor response
        And I get from companies service get debtor response
        And I get from limit service get debtor limit successful response for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
        When I send a GET request to "/debtors?search=ext"
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
            "total": 1,
            "items": [
                {
                    "id":"ad74bbc4-509e-47d5-9b50-a0320ce3d715",
                    "external_code":"ext_id",
                    "name":"Test User Company",
                    "financing_limit":7500,
                    "financing_power":4500,
                    "bank_account_iban":"DE1234",
                    "bank_account_bic":"BICISHERE",
                    "created_at":"2019-01-01T12:00:00+0100"
                }
            ]
        }
        """

    Scenario: Get merchant debtors overview, filtering by code (but no results)
        And I have a created order "XF43Y" with amounts 800/800/0, duration 30 and comment "test order"
        And I get from payments service get debtor response
        And I get from companies service get debtor response
        When I send a GET request to "/debtors?search=foobar"
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
            "total": 0,
            "items": []
        }
        """
