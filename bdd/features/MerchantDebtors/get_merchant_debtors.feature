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
        And the debtor has an information change request with state complete
        And GraphQL will respond to getPadMerchantDebtors with 200 and responses:
        """
        [
            {
                "data": {
                    "getPadMerchantDebtors": [
                        {
                            "total": 1
                        }
                    ]
                }
            },
            {
                "data": {
                    "getPadMerchantDebtors": [
                        {
                            "id": "1",
                            "merchant_id": 1,
                            "uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
                            "debtor_id": 1,
                            "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
                            "payment_debtor_id": "9e06e31b-eb65-4e1e-9c96-2f3fc40f4bad",
                            "score_thresholds_configuration_id": null,
                            "created_at": "2019-01-01T12:00:00Z",
                            "updated_at": "2019-01-01T12:00:00Z",
                            "debtor_information_change_request_state": null
                        }
                    ]
                }
            }
        ]
        """
        When I send a GET request to "/debtors"
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
                    "bank_account_iban":"DE27500105171416939916",
                    "bank_account_bic":"BICISHERE",
                    "debtor_information_change_request_state":"complete"
                }
            ]
        }
        """
