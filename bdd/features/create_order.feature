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
      | available_financing_limit |
      | amount                    |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified         |
      | debtor_identified_strict  |
      | debtor_is_trusted         |
      | limit                     |
      | debtor_not_customer       |
      | debtor_blacklisted        |
      | debtor_overdue            |
      | company_b2b_score         |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
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
    And the response status code should be 201
    And the JSON response should be:
    """
    {
       "external_code":"A1",
       "state":"declined",
       "reasons":[
          "debtor_not_identified"
       ],
       "amount":1000.00,
       "amount_net": 900.00,
       "amount_tax": 100.00,
       "debtor_company":{
          "name":null,
          "house_number":null,
          "street":null,
          "postal_code":null,
          "city":null,
          "country":null
       },
       "bank_account":{
          "iban":null,
          "bic":null
       },
       "invoice":{
          "number":null,
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
       }
    }
    """

  Scenario: Successful order creation
    Given I get from companies service identify match and good decision response
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
    Then the order A1 is in state created
    And the response status code should be 201
    And the JSON response should be:
    """
    {
      "external_code":"A1",
      "state":"created",
      "reasons":null,
      "amount":1000.00,
      "amount_net": 900.00,
      "amount_tax": 100.00,
      "debtor_company":{
        "name":"Test User Company",
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "postal_code":"10179",
        "city":"Berlin",
        "country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "number":null,
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
        "city": "Paris",
        "postal_code":"98765",
        "country":"DE"
     }
    }
    """
    And the order "A1" has the same hash "testusercompanyva2223333somenumbersomelegalberlin10179heinrichheineplatz10de"

  Scenario: Successful order creation without house
    Given I get from companies service identify match and good decision response
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
    Then print last JSON response
    Then the order A1 is in state created
    And the response status code should be 201
    And the JSON response should be:
    """
    {
      "external_code":"A1",
      "state":"created",
      "reasons":null,
      "amount":1000.00,
      "amount_net": 900.00,
      "amount_tax": 100.00,
      "debtor_company":{
        "name":"Test User Company",
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "postal_code":"10179",
        "city":"Berlin",
        "country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "number":null,
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
        "address_house":null,
        "industry_sector":"SOME SECTOR",
        "merchant_customer_id":"12"
     },
     "duration":30,
     "dunning_status": null,
     "shipped_at":null,
     "delivery_address":{
        "house_number":"22",
        "street":"Charlot strasse",
        "city": "Paris",
        "postal_code":"98765",
        "country":"DE"
     }
    }
    """
    And the order "A1" has the same hash "testusercompanyva2223333somenumbersomelegalberlin10179heinrichheineplatzde"

    Scenario: Successful order creation with a not german postal code in shipping
    Given I get from companies service identify match and good decision response
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
          "house_number":"22",
          "street":"An der Ronne",
          "city":"Vienna",
          "postal_code":"AT-5130-123333",
          "country":"AT"
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
    And the response status code should be 201
    And the JSON response should be:
    """
    {
      "external_code":"A1",
      "state":"created",
      "reasons":null,
      "amount":1000.00,
      "amount_net": 900.00,
      "amount_tax": 100.00,
      "created_at":"2019-06-06T16:21:53+0200",
      "debtor_company":{
        "name":"Test User Company",
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "postal_code":"10179",
        "city":"Berlin",
        "country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "number":null,
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
        "street":"An der Ronne",
        "city":"Vienna",
        "postal_code":"AT-5130-123333",
        "country":"AT"
       }
    }
    """
    And the response status code should be 201


  Scenario: Successful order creation without delivery_address.house_number
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
          "street":"Moulin Rouge Str.",
          "city":"Paris",
          "postal_code":"98765",
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
    And the response status code should be 201
    And the JSON response should be:
    """
    {
      "external_code":"A123",
      "state":"created",
      "reasons":null,
      "amount":43.3,
      "amount_net": 33.2,
      "amount_tax": 10.10,
      "debtor_company":{
        "name":"Test User Company",
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "postal_code":"10179",
        "city":"Berlin",
        "country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "number":null,
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
          "house_number":null,
          "street":"Moulin Rouge Str.",
          "city":"Paris",
          "postal_code":"98765",
          "country":"DE"
       }
    }
    """
    And the order "A123" has the same hash "testusercompanyva2223333somenumbersomelegalberlin10179heinrichheineplatz10de"


  Scenario: Successful order creation using lowercase country
    Given I get from companies service identify match and good decision response
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
          "gross":1000.00,
          "tax":100.00
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the order A1 is in state created
    And the response status code should be 201
    And the JSON response should be:
    """
    {
      "external_code":"A1",
      "state":"created",
      "reasons":null,
      "amount":1000.00,
      "amount_net": 900.00,
      "amount_tax": 100.00,
      "debtor_company":{
        "name":"Test User Company",
        "house_number":"10",
        "street":"Heinrich-Heine-Platz",
        "postal_code":"10179",
        "city":"Berlin",
        "country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "invoice":{
        "number":null,
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
          "country":"de"
       }
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
    And the response status code should be 201
    And the JSON response should be:
    """
    {
       "external_code":"A1",
       "state":"declined",
       "reasons":[
          "risk_policy"
       ],
       "amount":1000.00,
       "amount_net": 900.00,
       "amount_tax": 100.00,
       "debtor_company":{
          "name":"Test User Company",
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "postal_code":"10179",
          "city":"Berlin",
          "country":"DE"
       },
       "bank_account":{
          "iban":null,
          "bic":null
       },
       "invoice":{
          "number":null,
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
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
    Then the response status code should be 201
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
    Then the response status code should be 201
    And the order A1 is in state declined

  Scenario: The order should be on a state created if the previous order was declined because of the amount exceeded
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
       "order_id":"A3"
    }
    """
    Then the order A3 is in state created
    And the response status code should be 201

  Scenario: Order stays in state new if debtor limit lock was unsuccessful
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I get from companies service "/debtor/1/lock" endpoint response with status 400 and body
    """
    """
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
            "net":33.2,
            "gross":43.30,
            "tax":10.10
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A3"
    }
    """
    Then the response status code should be 500
    And the order A3 is in state new

  Scenario: Successful order creation without providing external code
    Given I get from companies service identify match and good decision response
    And I get from companies service "/debtor/1/lock" endpoint response with status 200 and body
    """
    """
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
         "duration":30
    }
    """
    Then the response status code should be 201

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
