Feature:
    In order to retrieve the merchant payments

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "Authorization" header equal to "Bearer someToken"
        And I get from Oauth service a valid user token
        And a merchant user exists with permission VIEW_PAYMENTS
        And GraphQL will respond to getPadPayments with 200 and responses:
        """
        [
            {
                "data": {
                    "getPadPayments": [
                        {
                            "total": 1
                        }
                    ]
                }
            },
            {
                "data": {
                    "getPadPayments": [{
                      "uuid": "98487055-cb4e-4922-a8a2-35dabe289d09",
                      "amount": 225.04,
                      "transaction_date": "2020-10-28",
                      "is_allocated": 1,
                      "overpaid_amount": 500,
                      "transaction_counterparty_iban": "DEFAKE123",
                      "transaction_counterparty_name": "Miro GmbH",
                      "transaction_reference": "Whisky Payment",
                      "merchant_debtor_uuid": "b08e1980-82a3-4735-8a76-ce9fa9672b54"
                    }]
                }
            }
        ]
        """

    Scenario: Get merchant payments details, extended for support
        When I send a GET request to "/public/payments"
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
          "items":[{
             "uuid":"98487055-cb4e-4922-a8a2-35dabe289d09",
             "amount":225.04,
             "transaction_date":"2020-10-28",
             "is_allocated":1,
             "transaction_counterparty_iban":"DEFAKE123",
             "transaction_counterparty_name":"Miro GmbH",
             "transaction_reference":"Whisky Payment",
             "merchant_debtor_uuid":"b08e1980-82a3-4735-8a76-ce9fa9672b54",
             "overpaid_amount":500
          }],
          "total": 1
        }
        """

    Scenario: List payment with all possible params
        When I send a GET request to "/public/payments?sort_by=transaction_date&sort_direction=asc&external_id=DE123SA&search=test&limit=5&filters[is_allocated]=1&filters[is_overpayment]=0"
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
          "items":[{
             "uuid":"98487055-cb4e-4922-a8a2-35dabe289d09",
             "amount":225.04,
             "transaction_date":"2020-10-28",
             "is_allocated":1,
             "transaction_counterparty_iban":"DEFAKE123",
             "transaction_counterparty_name":"Miro GmbH",
             "transaction_reference":"Whisky Payment",
             "merchant_debtor_uuid":"b08e1980-82a3-4735-8a76-ce9fa9672b54",
             "overpaid_amount":500
          }],
          "total": 1
        }
        """

    Scenario: I fail to get payment list by payment_debtor_uuid which is not valid
        When I send a GET request to "/public/payments?merchant_debtor_uuid=not_valid_uuid"
        Then the response status code should be 400

    Scenario: I fail to get payments results if I search with invalid transaction_uuid
        When I send a GET request to "/public/payments?transaction_uuid=1234-1234-1234-12344not_valid"
        Then the response status code should be 400



