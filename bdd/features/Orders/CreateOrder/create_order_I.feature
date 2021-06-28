Feature:
  In order to create an order
  I send the order data to the endpoint
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following risk check definitions exist:
      | name                      |
      | available_financing_limit  |
      | amount                    |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified          |
      | debtor_identified_strict   |
      | delivery_address          |
      | debtor_is_trusted         |
      | limit                     |
      | debtor_not_customer       |
      | debtor_blacklisted        |
      | debtor_overdue            |
      | company_b2b_score         |
      | line_items                |
      | fraud_score               |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | line_items                | 1       | 1                  |
      | available_financing_limit  | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified          | 1       | 1                  |
      | delivery_address          | 1       | 1                  |
      | debtor_identified_strict   | 1       | 1                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
      | fraud_score               | 1       | 0                  |
    And I get from Fraud service a non fraud response
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: Debtor identification failed
    Given I get from companies service identify no match response
    When I send a POST request to "/public/order" with body:
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
            "name":"billie GmbH",
            "address_addition":"left door",
            "address_house_number":"33",
            "address_street":"c/Velarus",
            "address_city":"Berlin",
            "address_postal_code":"12345",
            "address_country":"DE",
            "tax_id":"VA222",
            "tax_number":"3333",
            "registration_court":"some court",
            "registration_number":" some number",
            "industry_sector":"some sector",
            "subindustry_sector":"some sub",
            "employees_number":"33",
            "legal_form":"some legal",
            "established_customer":1
         },
         "delivery_address":{
            "house_number":"22",
            "street":"Charlot strasse",
            "city":"Paris",
            "postal_code":"98765",
            "country":"DE"
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
    Then the order A1 is in state declined
    And the order A1 workflow name is order_v1
    And the response status code should be 200
    And the JSON response should be:
    """
    {
       "order_id":"A1",
       "state":"declined",
       "reasons":"debtor_not_identified",
       "decline_reason":"debtor_not_identified",
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
          "name":null,
          "address_house_number":null,
          "address_street":null,
          "address_postal_code":null,
          "address_city":null,
          "address_country":null
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
       "debtor_external_data":{
          "merchant_customer_id":"12",
          "name":"billie GmbH",
          "address_country":"DE",
          "address_city":"Berlin",
          "address_postal_code":"12345",
          "address_street":"c\/Velarus",
          "address_house":"33",
          "industry_sector":"SOME SECTOR"
       },
       "delivery_address":{
          "house_number":"22",
          "street":"Charlot strasse",
          "city":"Paris",
          "postal_code":"98765",
          "country":"DE"
       },
       "billing_address":{
          "house_number":"33",
          "street":"c\/Velarus",
          "city":"Berlin",
          "postal_code":"12345",
          "country":"DE"
       },
       "created_at":"2020-02-18T12:27:24+0100",
       "shipped_at":null,
       "debtor_uuid":null
    }
    """

  Scenario: Successful order creation
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
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
    Then the order A1 is in state created
    And the order A1 workflow name is order_v1
    And the order A1 has creation source "api"
    And the response status code should be 200
    And the JSON response should be file "create_order_response.json"
    And the order "A1" has the same hash "test user company va222 3333 some number some legal berlin 10179 heinrich-heine-platz 10 de"

  Scenario: Successful order creation without house and workflow v2
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I get from payments service register debtor positive response
    When I send a POST request to "/public/api/v2/orders" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"",
          "last_name":"else",
          "phone_number":"+491234567",
          "email":"someone@billie.io"
       },
       "debtor":{
          "merchant_customer_id":"12",
          "name":"Test User Company",
          "company_address": {
            "house_number":null,
            "street":"Heinrich-Heine-Platz 10",
            "city":"Berlin",
            "postal_code":"10179",
            "country":"DE"
          },
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
          "house_number":null,
          "street":"Heinrich-Heine-Platz 10",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "amount":{
          "net":900.00,
          "gross":1000.00,
          "tax":100.00
       },
       "comment":"Some comment",
       "duration":30,
       "external_code":"A1"
    }
    """
    Then the order A1 is in state created
    And the order A1 workflow name is order_v2
    And the response status code should be 200
    And the JSON response should be:
    """
    {
     "external_code":"A1",
     "uuid":"362d4b04-95da-4351-ad96-039da13b7e17",
     "state":"created",
     "decline_reason":null,
     "amount":{
        "gross":1000,
        "net":900,
        "tax":100
     },
     "unshipped_amount":{
        "gross":1000,
        "net":900,
        "tax":100
     },
     "invoices":[],
     "duration":30,
     "created_at":"2021-06-11 16:48:33",
     "delivery_address":{
        "street":"Heinrich-Heine-Platz",
        "house_number":"10",
        "postal_code":"10179",
        "city":"Berlin",
        "country":"DE"
     },
     "debtor":{
        "name":"Test User Company",
        "company_address":{
           "street":"Heinrich-Heine-Platz",
           "house_number":"10",
           "postal_code":"10179",
           "city":"Berlin",
           "country":"DE"
        },
        "billing_address":{
           "street":"Heinrich-Heine-Platz",
           "house_number":"10",
           "postal_code":"10179",
           "city":"Berlin",
           "country":"DE"
        },
        "bank_account":{
           "iban":"DE1234",
           "bic":"BICISHERE"
        },
        "external_data":{
           "merchant_customer_id":"12",
           "name":"Test User Company",
           "industry_sector":"SOME SECTOR",
           "address":{
              "street":"Heinrich-Heine-Platz",
              "house_number":"10",
              "postal_code":"10179",
              "city":"Berlin",
              "country":"DE"
           }
        }
     },
     "invoices":[
     ]
    }
    """
    And the order "A1" has the same hash "test user company va222 3333 some number some legal berlin 10179 heinrich-heine-platz 10 de"

  Scenario: Debtor is not eligible for Point Of Sale
    Given I get from companies service identify match response
    And I get from scoring service bad debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
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
            "registration_court":"some court",
            "registration_number":" some number",
            "industry_sector":"some sector",
            "subindustry_sector":"some sub",
            "employees_number":"33",
            "legal_form":"some legal",
            "established_customer":1
         },
         "delivery_address":{
            "house_number":"22",
            "street":"Charlot strasse",
            "city":"Paris",
            "postal_code":"98765",
            "country":"DE"
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
    Then the order A1 is in state declined
    And the response status code should be 200
    And the JSON response should be:
    """
    {
       "order_id":"A1",
       "state":"declined",
       "reasons":"risk_policy",
       "decline_reason":"risk_policy",
       "amount":1000,
       "amount_net":900,
       "amount_tax":100,
       "duration":30,
       "dunning_status":null,
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
       "unshipped_amount":1000,
       "unshipped_amount_net":900,
       "unshipped_amount_tax":100,
       "workflow_name":"order_v1",
       "due_date":"2021-01-13",
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
          "house_number":"22",
          "street":"Charlot strasse",
          "city":"Paris",
          "postal_code":"98765",
          "country":"DE"
       },
       "billing_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "created_at":"2020-02-18T12:34:51+0100",
       "shipped_at":null,
       "debtor_uuid":null
    }
    """

  Scenario: Missing required fields
    When I send a POST request to "/order" with body:
      """
        {
           "debtor_person":{
              "salutation":"f",
              "first_name":"",
              "last_name":"else",
              "phone_number":"+491234567"
           },
           "debtor_company":{
              "merchant_customer_id":"12",
              "name":"billie GmbH",
              "address_addition":"left door",
              "address_house_number":"33",
              "address_street":"c/Velarus",
              "address_city": null,
              "tax_id":"VA222",
              "tax_number":"3333",
              "registration_court":"some court",
              "registration_number":" some number",
              "industry_sector":"some sector",
              "subindustry_sector":"some sub",
              "employees_number":"33",
              "legal_form":"some legal",
              "established_customer":1
           },
           "delivery_address":{
              "house_number":"22",
              "street":"Charlot strasse",
              "city":"Paris",
              "postal_code":"98765",
              "country":"DE"
           },
           "comment":"Some comment"
        }
      """
    Then print last JSON response
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
         "errors":[
            {
              "title": "This value should not be blank.",
              "code": "request_validation_error",
              "source": "amount.gross"
            },
            {
              "title": "This value should not be blank.",
              "code": "request_validation_error",
              "source": "amount.net"
            },
            {
              "title": "This value should not be blank.",
              "code": "request_validation_error",
              "source": "amount.tax"
            },
            {
               "source":"duration",
               "title":"This value should be between 1 and 120.",
               "code":"request_validation_error"
            },
            {
               "source":"debtor_company.address_city",
               "title":"This value should not be blank.",
               "code":"request_validation_error"
            },
            {
               "source":"debtor_company.address_postal_code",
               "title":"This value should not be blank.",
               "code":"request_validation_error"
            },
            {
               "source":"debtor_company.address_country",
               "title":"This value should not be blank.",
               "code":"request_validation_error"
            },
            {
               "source":"debtor_person.email",
               "title":"This value should not be blank.",
               "code":"request_validation_error"
            }
         ]
      }
    """
