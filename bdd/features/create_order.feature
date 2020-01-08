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
    And I get from companies service get debtor response
    And I get from payments service get debtor response

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
    And the response status code should be 200
    And the JSON response should be:
    """
    {
       "order_id":"A1",
       "state":"declined",
       "reasons":"debtor_not_identified",
       "decline_reason":"debtor_not_identified",
       "amount":1000.00,
       "amount_net": 900.00,
       "amount_tax": 100.00,
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
       "invoice":{
          "invoice_number":null,
          "payout_amount":null,
          "outstanding_amount":null,
          "fee_amount":null,
          "fee_rate":null,
          "due_date":null
       },
       "debtor_external_data":{
          "name":"billie GmbH",
          "address_country":"DE",
          "address_city": "Berlin",
          "address_postal_code":"12345",
          "address_street":"c\/Velarus",
          "address_house":"33",
          "industry_sector":"SOME SECTOR",
          "merchant_customer_id":"12"
       },
       "duration":30,
       "dunning_status": null,
       "shipped_at":null,
       "delivery_address":{
          "house_number":"22",
          "street":"Charlot strasse",
          "city":"Paris",
          "postal_code":"98765",
          "country":"DE"
       },
       "billing_address":{
          "house_number":"33",
          "street":"c/Velarus",
          "city": "Berlin",
          "postal_code":"12345",
          "country":"DE"
       }
    }
    """

  Scenario: Successful order creation
    Given I get from companies service identify match and good decision response
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
    And the response status code should be 200
    And the JSON response should be:
    """
    {
      "order_id":"A1",
      "state":"created",
      "reasons":null,
      "decline_reason":null,
      "amount":1000.00,
      "amount_net": 900.00,
      "amount_tax": 100.00,
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "invoice_number":null,
        "payout_amount":null,
        "outstanding_amount":null,
        "fee_amount":null,
        "fee_rate":null,
        "due_date":null
      },
      "debtor_external_data":{
        "name":"Test User Company",
        "address_country":"DE",
        "address_city": "Berlin",
        "address_postal_code":"10179",
        "address_street":"Heinrich-Heine-Platz",
        "address_house":"10",
        "industry_sector":"SOME SECTOR",
        "merchant_customer_id":"12"
     },
     "duration":30,
     "dunning_status": null,
     "shipped_at":null,
     "delivery_address":{
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "postal_code":"10179",
        "city":"Berlin",
        "country":"DE"
     },
     "billing_address":{
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "city": "Berlin",
        "postal_code":"10179",
        "country":"DE"
     }
    }
    """
    And the order "A1" has the same hash "test user company va222 3333 some number some legal berlin 10179 heinrich-heine-platz 10 de"

  Scenario: Successful order creation without house
    Given I get from companies service identify match and good decision response
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
          "address_house_number":null,
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
          "house_number":null,
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
    And the response status code should be 200
    And the JSON response should be:
    """
    {
      "order_id":"A1",
      "state":"created",
      "reasons":null,
      "decline_reason":null,
      "amount":1000,
      "amount_net": 900,
      "amount_tax": 100,
      "duration":30,
      "dunning_status": null,
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "invoice_number":null,
        "payout_amount":null,
        "outstanding_amount":null,
        "fee_amount":null,
        "fee_rate":null,
        "due_date":null
      },
      "debtor_external_data":{
        "merchant_customer_id":"12",
        "name":"Test User Company",
        "address_country":"DE",
        "address_city": "Berlin",
        "address_postal_code":"10179",
        "address_street":"Heinrich-Heine-Platz",
        "address_house":null,
        "industry_sector":"SOME SECTOR"
     },
     "delivery_address":{
        "house_number":null,
        "street":"Heinrich-Heine-Platz",
        "city":"Berlin",
        "postal_code":"10179",
        "country":"DE"
     },
     "billing_address":{
        "house_number":null,
        "street":"Heinrich-Heine-Platz",
        "city": "Berlin",
        "postal_code":"10179",
        "country":"DE"
     }
    }
    """
    And the order "A1" has the same hash "test user company va222 3333 some number some legal berlin 10179 heinrich-heine-platz de"

  Scenario: Successful order creation without delivery_address.house_number
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    When I send a POST request to "/order" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"someone",
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
          "city": "Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "amount":{
          "net":33.2,
          "gross":43.30,
          "tax":10.10
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A123"
    }
    """
    Then the order A123 is in state created
    And the response status code should be 200
    And the JSON response should be:
    """
    {
      "order_id":"A123",
      "state":"created",
      "reasons":null,
      "decline_reason":null,
      "amount":43.3,
      "amount_net": 33.2,
      "amount_tax": 10.10,
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "invoice_number":null,
        "payout_amount":null,
        "outstanding_amount":null,
        "fee_amount":null,
        "fee_rate":null,
        "due_date":null
      },
      "debtor_external_data":{
        "name":"Test User Company",
        "address_country":"DE",
        "address_city": "Berlin",
        "address_postal_code":"10179",
        "address_street":"Heinrich-Heine-Platz",
        "address_house":"10",
        "industry_sector":"SOME SECTOR",
        "merchant_customer_id":"12"
       },
       "duration":30,
       "dunning_status": null,
       "shipped_at":null,
       "delivery_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city": "Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "billing_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city": "Berlin",
          "postal_code":"10179",
          "country":"DE"
       }
    }
    """
    And the order "A123" has the same hash "test user company va222 3333 some number some legal berlin 10179 heinrich-heine-platz 10 de"

  Scenario: Successful order creation using lowercase country
    Given I get from companies service identify match and good decision response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I get from payments service register debtor positive response
    When I send a POST request to "/order" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"something",
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
          "address_country":"de",
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
          "country":"de"
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
    And the response status code should be 200
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
          "iban":"DE1234",
          "bic":"BICISHERE"
       },
       "invoice":{
          "invoice_number":null,
          "payout_amount":null,
          "outstanding_amount":null,
          "fee_amount":null,
          "fee_rate":null,
          "due_date":null
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
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"de"
       },
       "billing_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "shipped_at":null
    }
    """

  Scenario: Debtor is not eligible for Point Of Sale
    Given I get from companies service identify match and bad decision response
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
       "amount":1000.00,
       "amount_net": 900.00,
       "amount_tax": 100.00,
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
       "invoice":{
          "invoice_number":null,
          "payout_amount":null,
          "outstanding_amount":null,
          "fee_amount":null,
          "fee_rate":null,
          "due_date":null
       },
       "debtor_external_data":{
          "name":"Test User Company",
          "address_country":"DE",
          "address_city": "Berlin",
          "address_postal_code":"10179",
          "address_street":"Heinrich-Heine-Platz",
          "address_house":"10",
          "industry_sector":"SOME SECTOR",
          "merchant_customer_id":"12"
       },
       "duration":30,
       "dunning_status": null,
       "shipped_at":null,
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
          "city": "Berlin",
          "postal_code":"10179",
          "country":"DE"
       }

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
           "amount":{
              "tax":10.10
           },
           "comment":"Some comment"
        }
      """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
         "errors":[
            {
               "source":"amount.net",
               "title":"This value should not be blank.",
               "code":"request_validation_error"
            },
            {
               "source":"amount.gross",
               "title":"This value should not be blank.",
               "code":"request_validation_error"
            },
            {
               "source":"duration",
               "title":"This value should be 1 or more.",
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

  Scenario: Duplicate order with same external ID
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
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
           "order_id":"CO123"
        }
      """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
        "errors":[
          {
            "source":"external_code",
            "title":"Order with code CO123 already exists",
            "code":"request_validation_error"
          }
        ]
      }
    """

  Scenario: Invalid values
    When I send a POST request to "/order" with body:
      """
        {
           "debtor_person":{
              "salutation":"m",
              "first_name":"",
              "last_name":"else",
              "phone_number":"+491234567",
              "email":"error"
           },
           "debtor_company":{
              "merchant_customer_id":"12",
              "name":"billie GmbH",
              "address_addition":"left door",
              "address_house_number":"4",
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
              "postal_code":"invalid",
              "country":"FR"
           },
           "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":100.00
           },
           "comment":"Some comment",
           "duration":1000,
           "order_id":"CO123"
        }
      """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
       "errors":[
          {
            "source":"duration",
            "title":"This value should be 120 or less.",
            "code":"request_validation_error"
          },
          {
            "source":"debtor_person.email",
            "title":"This value is not a valid email address.",
            "code":"request_validation_error"
          }
       ]
      }
    """

  Scenario: Use debtor company address as delivery address if no delivery address was provided
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
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

  Scenario: Order exceeds the merchant available financing limit
    Given I get from companies service identify match response
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
            "house_number":"22",
            "street":"Charlot strasse",
            "city":"Paris",
            "postal_code":"98765",
            "country":"DE"
         },
         "amount":{
            "net":18000,
            "gross":20000,
            "tax":2000
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
      }
      """
    Then the response status code should be 200
    And the order A1 is in state declined

  Scenario: The order should be on a state created if the previous order was declined because of the amount exceeded
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I have a declined order "XF43Y" with amounts 90000/92000/1900, duration 30 and comment "test order"
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
       "order_id":"A3"
    }
    """
    Then the order A3 is in state created
    And the response status code should be 200

  Scenario: Successful order creation without providing external code
    Given I get from companies service identify match and good decision response
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
         "duration":30
    }
    """
    Then the response status code should be 200

  Scenario: Invalid order amounts, gross != net + tax
    When I send a POST request to "/order" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"something",
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
          "house_number":"22",
          "street":"Charlot strasse",
          "city":"Paris",
          "postal_code":"98765",
          "country":"de"
       },
       "amount":{
          "net":900.00,
          "gross":200.00,
          "tax": 0.00
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
       "errors":[
          {
             "source":"amount.net",
             "title":"Invalid amounts",
             "code":"request_validation_error"
          },
          {
             "source":"amount.gross",
             "title":"Invalid amounts",
             "code":"request_validation_error"
          },
          {
             "source":"amount.tax",
             "title":"Invalid amounts",
             "code":"request_validation_error"
          }
       ]
    }
    """

  Scenario: Successful order creation without providing industry_sector
    Given I get from companies service identify match and good decision response
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
    Then the response status code should be 200

  Scenario: Successful order creation (delivery and company address mismatch and Order amount < 250)
    Given I get from companies service identify match and good decision response
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
          "house_number":"4",
          "street":"somestr.",
          "city":"Berlin",
          "postal_code":"10000",
          "country":"DE"
       },
       "amount":{
          "net":50.00,
          "gross":60.00,
          "tax":10.00
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the order A1 is in state created
    And the response status code should be 200

  Scenario: Order declined (delivery and company address mismatch and Order amount > 250)
    Given I get from companies service identify match and good decision response
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
          "house_number":"4",
          "street":"somestr.",
          "city":"Berlin",
          "postal_code":"10000",
          "country":"DE"
       },
       "amount":{
          "net":500.00,
          "gross":600.00,
          "tax":100.00
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the order A1 is in state declined
    And the response status code should be 200

  Scenario: Successful order creation with line items
    Given I get from companies service identify match and good decision response
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
       "order_id":"A1",
       "line_items": [
          {
            "external_id": "SKU111",
            "title": "Iphone XS Max",
            "description": "Test test",
            "quantity": 1,
            "category": "mobile_phones",
            "brand": "Apple",
            "gtin": "test GTIN",
            "mpn": "test MPN",
            "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":100.00
            }
          }
       ]
    }
    """
    Then the order A1 is in state created
    And the response status code should be 200

  Scenario: Successful order creation with line items (missing optional line item fields)
    Given I get from companies service identify match and good decision response
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
       "order_id":"A1",
       "line_items": [
          {
            "external_id": "SKU111",
            "title": "Iphone XS Max",
            "description": "Test test",
            "quantity": 1,
            "category": "mobile_phones",
            "brand": "Apple",
            "gtin": "test GTIN",
            "mpn": "test MPN",
            "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":100.00
            }
          },
          {
            "external_id": "SKU22222",
            "title": "Iphone 6",
            "quantity": 1,
            "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":100.00
            }
          }
       ]
    }
    """
    Then the order A1 is in state created
    And the response status code should be 200

  Scenario: Failed order creation with line items (missing required line item fields and invalid data)
    Given I get from companies service identify match and good decision response
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
       "order_id":"A1",
       "line_items": [
          {
            "external_id": null,
            "title": "",
            "description": "Test test",
            "quantity": 0,
            "category": "mobile_phones",
            "brand": "Apple",
            "gtin": "test GTIN",
            "mpn": "test MPN"
          }
       ]
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
         "errors":[
            {
               "title":"This value should not be blank.",
               "code":"request_validation_error",
               "source":"line_items[0].external_id"
            },
            {
               "title":"This value should not be blank.",
               "code":"request_validation_error",
               "source":"line_items[0].title"
            },
            {
               "title":"This value should be greater than or equal to 1.",
               "code":"request_validation_error",
               "source":"line_items[0].quantity"
            },
            {
               "title":"This value should not be blank.",
               "code":"request_validation_error",
               "source":"line_items[0].amount.net"
            },
            {
               "title":"This value should not be blank.",
               "code":"request_validation_error",
               "source":"line_items[0].amount.gross"
            },
            {
               "title":"This value should not be blank.",
               "code":"request_validation_error",
               "source":"line_items[0].amount.tax"
            }
         ]
      }
    """

  Scenario: Failed order creation with line items (invalid data)
    Given I get from companies service identify match and good decision response
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
       "order_id":"A1",
       "line_items": [
          {
            "external_id": 1,
            "title": null,
            "description": "Test test",
            "quantity": "test",
            "category": "mobile_phones",
            "brand": "Apple",
            "gtin": "test GTIN",
            "mpn": "test MPN",
            "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":100.00
            }
          }
       ]
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "line_items[0].title"
        },
        {
          "title": "This value should be greater than or equal to 1.",
          "code": "request_validation_error",
          "source": "line_items[0].quantity"
        }
      ]
    }
    """

  Scenario: Failed order creation with line items (invalid line item amount)
    Given I get from companies service identify match and good decision response
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
       "order_id":"A1",
       "line_items": [
          {
            "external_id": "SKU111",
            "title": "Iphone XS Max",
            "description": "Test test",
            "quantity": 1,
            "category": "mobile_phones",
            "brand": "Apple",
            "gtin": "test GTIN",
            "mpn": "test MPN",
            "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":10.0
            }
          }
       ]
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "Invalid amounts",
          "code": "request_validation_error",
          "source": "line_items[0].amount.net"
        },
        {
          "title": "Invalid amounts",
          "code": "request_validation_error",
          "source": "line_items[0].amount.gross"
        },
        {
          "title": "Invalid amounts",
          "code": "request_validation_error",
          "source": "line_items[0].amount.tax"
        }
      ]
    }
    """

  Scenario: Fail to create a good order if I have a public domain email and the line items contain a public domain
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    When I send a POST request to "/order" with body:
    """
    {
       "debtor_person":{
          "salutation":"m",
          "first_name":"",
          "last_name":"else",
          "phone_number":"+491234567",
          "email":"someone@gmail.com"
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
       "order_id":"A1",
       "line_items": [
          {
            "external_id": "SKU111",
            "title": "Iphone XS Max Downloadable content here careful!",
            "description": "Test test",
            "quantity": 1,
            "category": "mobile_phones",
            "brand": "Apple",
            "gtin": "test GTIN",
            "mpn": "test MPN",
            "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":100.00
            }
          },
          {
            "external_id": "SKU22222",
            "title": "Iphone 6",
            "quantity": 1,
            "amount":{
              "net":900.00,
              "gross":1000.00,
              "tax":100.00
            }
          }
       ]
    }
    """
    Then the order A1 is in state declined
    And the response status code should be 200

  Scenario: Authorised oauth user without a Merchant Api-Key authentication cannot create orders
    Given a merchant user exists with permission CONFIRM_ORDER_PAYMENT
    And I get from Oauth service a valid user token
    And I add "X-Api-Key" header equal to invalid_key
	And I add "Authorization" header equal to "Bearer someToken"
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
    Then the response status code should be 403
    And the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """
