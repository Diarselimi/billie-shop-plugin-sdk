Feature:
  In order to retrieve the order details
  I want to call the get order endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following risk check definitions exist:
      | name                      |
      | available_financing_limit |
      | amount                    |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified         |
      | debtor_identified_strict  |
      | delivery_address          |
      | debtor_is_trusted         |
      | limit                     |
      | debtor_not_customer       |
      | debtor_blacklisted        |
      | company_b2b_score         |
      | line_items                |
      | fraud_score               |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | line_items                | 1       | 1                  |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 0                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | delivery_address          | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
      | fraud_score               | 1       | 0                  |
    And I get from Banco service search bank good response

  Scenario: Unsuccessful order retrieve - order doesn't exist
    When I send a GET request to "/order/ABC"
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
    """

  Scenario: Successful declined order retrieval
    Given I have orders with the following data
      | external_id | state    | gross | net | tax | duration | comment    | payment_uuid                         |
      | XF43Y       | declined | 1000  | 900 | 100 | 30       | test order | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 |
    And The following risk check results exist for order "XF43Y":
      | check_name  | is_passed |
      | fraud_score | 0         |
      | limit       | 0         |
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from invoice-butler service good response no CreditNotes
    And I get from payments service get order details response
    And I get from Sepa service get mandate valid response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "order_id": "XF43Y",
        "uuid": "test123",
        "state": "declined",
        "reasons": "risk_policy",
        "decline_reason": "risk_policy",
        "amount": 1000,
        "amount_net": 900,
        "amount_tax": 100,
        "created_at": "2019-05-20T13:00:00+0200",
        "unshipped_amount":1000,
        "unshipped_amount_net":900,
        "unshipped_amount_tax":100,
        "debtor_company": {
            "name": "Test User Company",
            "address_house_number": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_postal_code": "10179",
            "address_city": "Berlin",
            "address_country": "DE"
        },
         "bank_account": {
            "iban": null,
            "bic": null
        },
        "invoices": [],
        "invoice": {
            "outstanding_amount": 500,
            "pending_merchant_payment_amount": 0,
            "fee_rate": 20,
            "fee_amount": 123.33,
            "pending_cancellation_amount": 0,
            "invoice_number": "some_code",
            "payout_amount": 123.33,
            "due_date": "2019-06-19"
        },
        "selected_payment_method": "direct_debit",
        "payment_methods": [
            {
                "type": "bank_transfer",
                "data": {
                    "iban": "DE27500105171416939916",
                    "bic": "BICISHERE",
                    "bank_name": "Mocked Bank Name GmbH"
                }
            },
            {
                "type":"direct_debit",
                "data":{
                    "iban":"DE42500105172497563393",
                    "bic":"DENTSXXX",
                    "bank_name":"Possum Bank",
                    "mandate_reference":"YGG6VI5RQ4OR3GJ0",
                    "mandate_execution_date":"2020-01-01 00:00:00",
                    "creditor_identification":"DE26ZZZ00001981599"
                }
            }
        ],
        "debtor_external_data": {
            "name": "test",
            "address_country": "TE",
            "address_city": "testCity",
            "address_postal_code": "test",
            "address_street": "test",
            "address_house": "test",
            "industry_sector": "test",
            "merchant_customer_id":"ext_id"
         },
         "duration": 30,
         "dunning_status": null,
         "shipped_at": null,
         "delivery_address":{
            "house_number":"test",
            "street":"test",
            "city": "test",
            "postal_code":"test",
            "country":"TE"
         },
         "billing_address":{
          "house_number":"test",
          "street":"test",
          "city":"test",
          "postal_code":"test",
          "country":"TE"
        },
        "debtor_uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
        "workflow_name": "order_v1"
    }
    """

  Scenario: Successful late order retrieval
    Given I have orders with the following data
      | external_id | state | gross | net | tax | duration | comment    | payment_uuid                         |
      | XF43Y       | late  | 1000  | 900 | 100 | 30       | test order | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 |
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from salesforce dunning status endpoint "Created" status for order "test-order-uuidXF43Y"
    And I get from invoice-butler service good response no CreditNotes
    And I get from payments service get order details response
    And I get from Sepa service get mandate valid response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
       "order_id":"XF43Y",
       "state":"late",
       "reasons":null,
       "decline_reason":null,
       "amount":1000,
       "amount_net":900,
       "amount_tax":100,
       "duration":30,
       "dunning_status":"active",
       "unshipped_amount":1000,
       "unshipped_amount_net":900,
       "unshipped_amount_tax":100,
       "due_date":"2021-01-13",
       "debtor_company":{
          "name":"Test User Company",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_postal_code":"10179",
          "address_city":"Berlin",
          "address_country":"DE"
       },
       "bank_account":{
          "iban":"DE27500105171416939916",
          "bic":"BICISHERE"
       },
       "invoices": [],
       "invoice": {
           "outstanding_amount": 500,
           "pending_merchant_payment_amount": 0,
           "fee_rate": 20,
           "fee_amount": 123.33,
           "pending_cancellation_amount": 0,
           "invoice_number": "some_code",
           "payout_amount": 123.33,
           "due_date": "2019-06-19"
       },
        "selected_payment_method": "direct_debit",
        "payment_methods": [
            {
                "type": "bank_transfer",
                "data": {
                    "iban": "DE27500105171416939916",
                    "bic": "BICISHERE",
                    "bank_name": "Mocked Bank Name GmbH"
                }
            },
            {
                "type":"direct_debit",
                "data":{
                    "iban":"DE42500105172497563393",
                    "bic":"DENTSXXX",
                    "bank_name":"Possum Bank",
                    "mandate_reference":"YGG6VI5RQ4OR3GJ0",
                    "mandate_execution_date":"2020-01-01 00:00:00",
                    "creditor_identification":"DE26ZZZ00001981599"
                }
            }
        ],
       "debtor_external_data":{
          "merchant_customer_id":"ext_id",
          "name":"test",
          "address_country":"TE",
          "address_city":"testCity",
          "address_postal_code":"test",
          "address_street":"test",
          "address_house":"test",
          "industry_sector":"test"
       },
       "delivery_address":{
          "house_number":"test",
          "street":"test",
          "city":"test",
          "postal_code":"test",
          "country":"TE"
       },
       "billing_address":{
          "house_number":"test",
          "street":"test",
          "city":"test",
          "postal_code":"test",
          "country":"TE"
       },
       "created_at":"2019-05-20T13:00:00+0200",
       "shipped_at":null,
       "debtor_uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
       "workflow_name": "order_v1"
    }
    """

  Scenario: Successful complete order retrieval
    Given I have orders with the following data
      | external_id | state    | gross | net | tax | duration | comment    | payment_uuid                         |
      | XF43Y       | complete | 1000  | 900 | 100 | 30       | test order | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 |
    And I get from invoice-butler service good response no CreditNotes
    And I get from payments service get order details response
    And I get from Sepa service get mandate valid response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    And GraphQL will respond to getMerchantDebtorDetails query
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "order_id": "XF43Y",
        "uuid": "test123",
        "state": "complete",
        "reasons": null,
        "decline_reason": null,
        "amount": 1000,
        "amount_net": 900,
        "amount_tax": 100,
        "duration": 30,
        "dunning_status": null,
        "unshipped_amount":1000,
        "unshipped_amount_net":900,
        "unshipped_amount_tax":100,
        "workflow_name":"order_v1",
        "due_date":"2021-01-13",
        "debtor_company": {
            "address_city": "Berlin",
            "address_country": "DE",
            "address_house_number": "10",
            "address_postal_code": "10179",
            "address_street": "Heinrich-Heine-Platz",
            "name": "Test User Company"
        },
        "bank_account": {
            "iban": "DE27500105171416939916",
            "bic": "BICISHERE"
        },
        "invoices": [],
         "invoice": {
           "outstanding_amount": 500,
           "pending_merchant_payment_amount": 0,
           "fee_rate": 20,
           "fee_amount": 123.33,
           "pending_cancellation_amount": 0,
           "invoice_number": "some_code",
           "payout_amount": 123.33,
           "due_date": "2019-06-19"
        },
      "selected_payment_method": "direct_debit",
      "payment_methods": [
        {
          "type": "bank_transfer",
          "data": {
            "iban": "DE27500105171416939916",
            "bic": "BICISHERE",
            "bank_name": "Mocked Bank Name GmbH"
          }
        },
        {
          "type":"direct_debit",
          "data":{
            "iban":"DE42500105172497563393",
            "bic":"DENTSXXX",
            "bank_name":"Possum Bank",
            "mandate_reference":"YGG6VI5RQ4OR3GJ0",
            "mandate_execution_date":"2020-01-01 00:00:00",
            "creditor_identification":"DE26ZZZ00001981599"
          }
        }
      ],
        "debtor_external_data": {
            "merchant_customer_id": "ext_id",
            "name": "test",
            "address_country": "TE",
            "address_city": "testCity",
            "address_postal_code": "test",
            "address_street": "test",
            "address_house": "test",
            "industry_sector": "test"
        },
        "delivery_address": {
            "house_number": "test",
            "street": "test",
            "city": "test",
            "postal_code": "test",
            "country": "TE"
        },
        "billing_address":{
          "house_number":"test",
          "street":"test",
          "city":"test",
          "postal_code":"test",
          "country":"TE"
        },
        "created_at": "2019-05-20T13:00:00+0200",
        "shipped_at": null,
        "debtor_uuid": "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
        "workflow_name": "order_v1"
    }
    """
