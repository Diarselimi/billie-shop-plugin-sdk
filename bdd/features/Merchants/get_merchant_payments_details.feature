Feature:
  In order to retrieve the merchant payments

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_PAYMENTS
    And I get from payments service get debtor response
    And I get from Sepa service for "9b1d7d4b-766d-4af1-b61f-e422359d13b2" mandate valid response
    And I get from invoice-butler service good response no CreditNotes
    And I get from payments service a transaction "c7be46c0-e049-4312-b274-258ec5aeeb71"
    And GraphQL will respond to getPadPaymentDetails with 200 and responses:
        """
        [
            {
                "data": {
                    "getPadPaymentDetails": [
                        {
                            "amount": 220.0,
                            "transaction_date": "2020-10-10",
                            "orders": [
                                {
                                    "amount": 250.0,
                                    "uuid": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4",
                                    "external_id": "external_id",
                                    "mapped_amount": 220.0,
                                    "outstanding_amount": 30.0,
                                    "invoice_number": "invoice_number"
                                },
                                 {
                                    "amount": 100,
                                    "uuid": null,
                                    "external_id": "external_id",
                                    "mapped_amount": 220.0,
                                    "outstanding_amount": 30.0,
                                    "invoice_number": "invoice_number"
                                }
                            ],
                            "is_allocated": 1,
                            "merchant_debtor_uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
                            "transaction_reference": "Reference",
                            "transaction_counterparty_name": "transaction_counterparty_name",
                            "transaction_counterparty_iban": "DEFAKE123",
                            "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb71"
                        }
                    ]
                }
            }
        ]
        """

  Scenario: Get merchant payments details, extended for support
    When I have a "created" order "test_external" with amounts 1000/900/100, duration 30 and checkout session "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4" and uuid "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4"
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    And I send a GET request to "/public/payments/c7be46c0-e049-4312-b274-258ec5aeeb71"
    And I get from invoice-butler payment methods response
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "amount": 220,
        "transaction_date": "2020-10-10 00:00:00",
        "payment_method": {
            "type": "direct_debit",
            "data": {
                "creditor_identification": "DE26ZZZ00001981599",
                "iban": "DE42500105172497563393",
                "bic": "DENTSXXX",
                "mandate_reference": "YGG6VI5RQ4OR3GJ0",
                "bank_name": "Possum Bank",
                "mandate_execution_date": "2017-03-15 00:00:00"
            }
        },
        "is_allocated": true,
        "merchant_debtor_uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
        "invoices": [
            {
                "amount": 123.33,
                "uuid": "208cfe7d-046f-4162-b175-748942d6cff4",
                "mapped_amount": 73.33,
                "order": {
                    "external_id": "test_external",
                    "workflow_name": "order_v1",
                    "uuid": "6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4"
                },
                "outstanding_amount": 50,
                "invoice_number": "some_code"
            }
        ],
        "transaction_reference": "Reference",
        "transaction_counterparty_name": "transaction_counterparty_name",
        "transaction_counterparty_iban": "DEFAKE123",
        "uuid": "c7be46c0-e049-4312-b274-258ec5aeeb71",
        "overpaid_amount": 146.67
    }
    """

