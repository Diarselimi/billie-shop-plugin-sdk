Feature:
  I create an order for whitelisted debtor so we skip scoring check

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following risk check definitions exist:
      | name                              |
      | available_financing_limit         |
      | amount                            |
      | debtor_country                    |
      | debtor_industry_sector            |
      | debtor_identified                 |
      | debtor_identified_strict          |
      | debtor_is_trusted                 |
      | limit                             |
      | debtor_not_customer               |
      | company_b2b_score                 |
      | line_items                        |
      | debtor_identified_billing_address |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name                   | enabled | decline_on_failure |
      | line_items                        | 1       | 1                  |
      | available_financing_limit         | 1       | 1                  |
      | amount                            | 1       | 1                  |
      | debtor_country                    | 1       | 1                  |
      | debtor_industry_sector            | 1       | 1                  |
      | debtor_identified                 | 1       | 1                  |
      | debtor_identified_billing_address | 1       | 1                  |
      | debtor_identified_strict          | 1       | 1                  |
      | debtor_is_trusted                 | 1       | 1                  |
      | limit                             | 1       | 1                  |
      | debtor_not_customer               | 1       | 1                  |
      | company_b2b_score                 | 1       | 1                  |
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from Banco service search bank good response

  Scenario: Successful order creation after company is identified by the billing address with at least one complete older order
    Given I have a complete order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service identify with billing address match response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I get from payments service register debtor positive response
    When I send a POST request to "/order" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"",
          "last_name":"else",
          "phone_number":"+491234567",
          "email":"someone@billie.io"
       },
       "debtor_company":{
          "merchant_customer_id":"12",
          "name":"Test User Company",
          "address_addition":"left door",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_city":"Berlin",
          "address_postal_code":"10179",
          "address_country":"DE",
          "tax_id":"VA222",
          "tax_number":"3333",
          "registration_court":"",
          "registration_number":" some number",
          "industry_sector":"some sector",
          "subindustry_sector":"some sub",
          "employees_number":"33",
          "legal_form":"some legal",
          "established_customer":1
       },
       "delivery_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "billing_address":{
          "house_number":"99",
          "street":"Deliver here",
          "city":"Paris",
          "postal_code":"98765",
          "country":"FR"
       },
       "amount":{
          "net":900.00,
          "gross":1000.00,
          "tax":100.00
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the response status code should be 200
    And the order A1 is in state created
    And the JSON response should be:
    """
    {
      "order_id":"A1",
      "state":"created",
      "reasons":null,
      "decline_reason":null,
      "amount":1000,
      "amount_net":900,
      "amount_tax":100,
      "unshipped_amount":1000,
      "unshipped_amount_net":900,
      "unshipped_amount_tax":100,
      "duration":30,
      "dunning_status":null,
      "workflow_name":"order_v1",
      "due_date":"2021-01-13",
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_city":"Berlin",
        "address_postal_code":"10179",
        "address_country":"DE"
      },
      "bank_account":{
        "iban":"DE27500105171416939916",
        "bic":"BICISHERE"
      },
      "invoices":[],
      "invoice":{
        "invoice_number":null,
        "payout_amount":null,
        "outstanding_amount":null,
        "fee_amount":null,
        "fee_rate":null,
        "due_date":null,
        "pending_merchant_payment_amount":null,
        "pending_cancellation_amount":null
      },
      "selected_payment_method": "bank_transfer",
      "payment_methods": [
        {
          "type": "bank_transfer",
          "data": {
            "iban": "DE27500105171416939916",
            "bic": "BICISHERE",
            "bank_name": "Mocked Bank Name GmbH"
          }
        }
      ],
      "debtor_external_data":{
        "merchant_customer_id":"12",
        "name":"Test User Company",
        "address_country":"DE",
        "address_city":"Berlin",
        "address_postal_code":"10179",
        "address_street":"Heinrich-Heine-Platz",
        "address_house":"10",
        "industry_sector":"SOME SECTOR"
      },
      "delivery_address":{
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "city":"Berlin",
        "postal_code":"10179",
        "country":"DE"
      },
      "billing_address":{
        "house_number":"99",
        "street":"Deliver here",
        "city":"Paris",
        "postal_code":"98765",
        "country":"FR"
      },
      "shipped_at":null,
      "debtor_uuid":null
    }
    """

  Scenario: Decline order because of billing address mismatch, after company is identified by the billing address with at least one complete older order
    Given I have a complete order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service identify with random billing address match response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I get from payments service register debtor positive response
    When I send a POST request to "/order" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"",
          "last_name":"else",
          "phone_number":"+491234567",
          "email":"someone@billie.io"
       },
       "debtor_company":{
          "merchant_customer_id":"12",
          "name":"Test User Company",
          "address_addition":"left door",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_city":"Berlin",
          "address_postal_code":"10179",
          "address_country":"DE",
          "tax_id":"VA222",
          "tax_number":"3333",
          "registration_court":"",
          "registration_number":" some number",
          "industry_sector":"some sector",
          "subindustry_sector":"some sub",
          "employees_number":"33",
          "legal_form":"some legal",
          "established_customer":1
       },
       "delivery_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "billing_address":{
          "house_number":"99",
          "street":"Deliver here",
          "city":"Paris",
          "postal_code":"98765",
          "country":"FR"
       },
       "amount":{
          "net":900.00,
          "gross":1000.00,
          "tax":100.00
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    And the JSON response should be:
    """
    {
      "order_id":"A1",
      "state":"declined",
      "reasons":"debtor_address",
      "decline_reason":"debtor_address",
      "amount":1000,
      "amount_net":900,
      "amount_tax":100,
      "unshipped_amount":1000,
      "unshipped_amount_net":900,
      "unshipped_amount_tax":100,
      "duration":30,
      "dunning_status":null,
      "workflow_name":"order_v1",
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
        "iban":null,
        "bic":null
      },
      "invoices":[],
      "invoice":{
        "invoice_number":null,
        "payout_amount":null,
        "outstanding_amount":null,
        "fee_amount":null,
        "fee_rate":null,
        "due_date":null,
        "pending_merchant_payment_amount":null,
        "pending_cancellation_amount":null
      },
      "selected_payment_method": "bank_transfer",
      "payment_methods": [
        {
          "type": "bank_transfer",
          "data": {
            "iban": "DE27500105171416939916",
            "bic": "BICISHERE",
            "bank_name": "Mocked Bank Name GmbH"
          }
        }
      ],
      "debtor_external_data":{
        "merchant_customer_id":"12",
        "name":"Test User Company",
        "address_country":"DE",
        "address_city":"Berlin",
        "address_postal_code":"10179",
        "address_street":"Heinrich-Heine-Platz",
        "address_house":"10",
        "industry_sector":"SOME SECTOR"
      },
      "delivery_address":{
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "city":"Berlin",
        "postal_code":"10179",
        "country":"DE"
      },
      "billing_address":{
        "house_number":"99",
        "street":"Deliver here",
        "city":"Paris",
        "postal_code":"98765",
        "country":"FR"
      },
      "shipped_at":null,
      "debtor_uuid":null
    }
    """
    Then the response status code should be 200
    And the order A1 is in state declined
