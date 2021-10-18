Feature: As a merchant, i should be able to create an order if I provide a valid session_id

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
      | limit                     |
      | debtor_not_customer       |
      | company_b2b_score         |
      | debtor_identified_strict  |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 0                  |
      | amount                    | 1       | 0                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
    And I get from companies service "/debtor/1" endpoint response with status 200 and body
      """
      {
        "id": 1,
        "payment_id": "test",
        "name": "Test User Company",
        "address_house": "10",
        "address_street": "Heinrich-Heine-Platz",
        "address_city": "Berlin",
        "address_postal_code": "10179",
        "address_country": "DE",
        "address_addition": null,
        "crefo_id": "123",
        "schufa_id": "123",
        "is_blacklisted": 0,
        "is_from_trusted_source": 0
      }
      """
    And I get from payments service get debtor response
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: The order gets declined because of the limit exceeded.
    Given I get from companies service identify match response with similar candidate
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service register debtor positive response
    And Debtor has insufficient limit
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
          "net":1133.2,
          "gross":1143.30,
          "tax":10.10
       },
       "comment":"Some comment",
       "duration":30,
       "dunning_status": null,
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
    And the JSON response should be:
    """
    {
      "state": "declined",
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "reasons":"debtor_limit_exceeded",
      "decline_reason":"debtor_limit_exceeded",
      "debtor_company_suggestion":{
        "address_city": "Berlin",
        "address_country": "DE",
        "address_house_number": "20",
        "address_postal_code": "10001",
        "address_street": "Otto-Braun-Str.",
        "name": "Foo Bar GmbH"
      }
    }
    """
    And the order A1 is in state declined
    And the response status code should be 200
    And the checkout_session_id "123123" should be valid
    And the order A1 has creation source "checkout"

  Scenario: I success if I try to create an order with a valid session_id, company identified via billing address.
    Given I get from companies service identify with billing address match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service register debtor positive response
    And Debtor has sufficient limit
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/public/checkout-session/123123/authorize" with body:
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
          "name":"Test User Company",
          "address_addition":"left door",
          "address_house_number":"4",
          "address_street":"Billing Street",
          "address_city":"Berlin",
          "address_postal_code":"10639",
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
       "dunning_status": null,
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
    Then the response status code should be 200
    And the order A1 is in state authorized
    And the checkout_session_id "123123" should be invalid
    And the JSON response should be:
    """
    {
      "state": "authorized",
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"4",
        "address_street":"Billing Street name",
        "address_postal_code":"10639",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "reasons":null,
      "decline_reason":null,
      "debtor_company_suggestion": null
    }
    """

  Scenario: I success if I try to create an order with a valid session_id
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service register debtor positive response
    And Debtor has sufficient limit
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/public/checkout-session/123123/authorize" with body:
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
          "name":"Test User Company",
          "address_addition":"left door",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine",
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
       "dunning_status": null,
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
    Then the response status code should be 200
    And the order A1 is in state authorized
    And the checkout_session_id "123123" should be invalid
    And the JSON response should be:
    """
    {
      "state": "authorized",
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "reasons":null,
      "decline_reason":null,
      "debtor_company_suggestion": null
    }
    """

  Scenario: I success if I try to create an order with a valid session_id and no house
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has sufficient limit
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/public/checkout-session/123123/authorize" with body:
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
          "name":"Test User Company",
          "address_addition":"left door",
          "address_house_number":"",
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
       "dunning_status": null,
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
    Then the order A1 is in state authorized
    And the response status code should be 200
    And the JSON response should be:
    """
    {
      "state": "authorized",
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "reasons":null,
      "decline_reason":null,
      "debtor_company_suggestion": null
    }
    """

  Scenario: An order goes to declined if we cannot identify the company
    Given I get from companies service identify no match response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "dunning_status": null,
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
    Then the order A1 is in state declined
    And the response status code should be 200
    And the JSON response should be:
    """
    {
      "state": "declined",
      "debtor_company":{
          "name":null,
          "address_house_number":null,
          "address_street":null,
          "address_postal_code":null,
          "address_city":null,
          "address_country":null
       },
      "reasons":"debtor_not_identified",
      "decline_reason":"debtor_not_identified",
      "debtor_company_suggestion":null
    }
    """

  Scenario: An order goes to pre_waiting if it doesn't pass all the soft checks
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And Debtor has sufficient limit
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
          "net":333333.2,
          "gross":666643.30,
          "tax":333310.10
       },
       "comment":"Some comment",
       "duration":30,
       "dunning_status": null,
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
    And the response status code should be 200
    Then the order A1 is in state pre_waiting

  Scenario: I success if I try to create an order with a valid session_id,
  but fail for the second time because the session_id should be invalidated for orders with authorized state!
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has sufficient limit
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "dunning_status": null,
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
    Then the order A1 is in state authorized
    And the response status code should be 200
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "dunning_status": null,
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
    Then the response status code should be 401
    And the JSON response should be:
    """
    {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: I success if I try to create an order with a valid session_id,
  but fail for the second time because the session_id should be invalidated for orders with pre_waiting state!
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And The "limit" merchant risk check for merchant "1" is configured as enabled = "1" and decline_on_failure = "0"
    And Debtor has insufficient limit
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "dunning_status": null,
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
    Then the order A1 is in state pre_waiting
    And the response status code should be 200
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "dunning_status": null,
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
    Then the response status code should be 401
    And the JSON response should be:
    """
    {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: I fail authorization if I try to create an order with a invalid session_id
    Given I have an already used checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "dunning_status": null,
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
    Then the JSON response should be:
    """
    {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: Trying to create an order without an existing session id
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
           "comment":"Some comment",
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
    Then the JSON response should be:
    """
    {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """

  Scenario: Trying to create a order without amount data
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "comment":"Some comment",
       "duration":30,
       "dunning_status": null,
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
    And the JSON response should be:
    """
    {
      "errors": [
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
        }
      ]
    }
    """
    And the response status code should be 400
