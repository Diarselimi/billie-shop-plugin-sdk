Feature: Initialize new checkout session
  In order to know if Billie should be displayed as a payment method
  As klarna scheme
  I want to have an endpoint to create an authorization (authorize a session)

  Background:
    And I add "X-Test" header equal to 1
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

  Scenario: Authorize call fails because session not found
    When I request "POST /authorizations" with body:
      """
      {
        "payment_method":
        {
          "payment_method_session_id": "142d6f47-cec2-4768-8de4-f7cbd1642bd1",
          "ui":
          {
            "data": {}
          }
        }
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Payment session not found" ]
      }
      """

  Scenario: Pre-authorize call is successful
    Given I have a checkout_session_id "142d6f47-cec2-4768-8de4-f7cbd1642bd1"
    When I request "POST /authorizations" with body:
      """
      {
        "amount": 300000,
        "tax_amount": 60000,
        "country": "DE",
        "currency": "EUR",
        "customer": {
          "type": "organization",
          "billing_address": {
            "organization_name": "Billie GmbH",
            "street_address": "Charlottenstr.",
            "street_address2": "4",
            "city": "Berlin",
            "postal_code": "10969",
            "country": "DE",
            "title": "Mr",
            "given_name": "Fefas",
            "family_name": "Possum",
            "phone": "+49158888890",
            "email": "info@billie.io"
          },
          "history": {
            "number_of_purchases": 14
          }
        },
        "expires_at": "2021-12-31T01:00:00Z",
        "final": true,
        "merchant": {
          "acquirer_merchant_id": "dummy"
        },
        "payment_method":
        {
          "payment_method_session_id": "142d6f47-cec2-4768-8de4-f7cbd1642bd1",
          "payment_method_id": "billie_pay_later",
          "ui":
          {
            "data": {}
          }
        },
        "purchase_details": {
          "locale": "DE",
          "shipping_address": {
            "street_address": "Charlottenstr.",
            "street_address2": "4",
            "city": "Berlin",
            "postal_code": "10969",
            "country": "DE"
          },
          "order_lines": [
            {
              "reference": "REF456",
              "name": "iPhone 18",
              "quantity": "2",
              "total_amount": "300000",
              "total_tax_amount": "60000",
              "product_identifiers": {
                "category_path": "path",
                "brand": "brand",
                "global_trade_item_number": "gtin",
                "manufacturer_part_number": "mpn"
              }
            }
          ]
        }
      }
      """
    Then the response is 200 with body:
      """
      {
        "result": "user_action_required",
        "payment_method":
        {
          "ui":
          {
            "data":
            {
              "amount":
              {
                "gross": 3000,
                "net": 2400,
                "tax": 600
              },
              "duration": 30,
              "delivery_address": {
                "street": "Charlottenstr.",
                "house_number": "4",
                "addition": "",
                "city": "Berlin",
                "postal_code": "10969",
                "country": "DE"
              },
              "debtor_company": {
                "name": "Billie GmbH",
                "established_customer": true,
                "address_street": "Charlottenstr.",
                "address_house_number": "4",
                "address_addition": "",
                "address_city": "Berlin",
                "address_postal_code": "10969",
                "address_country": "DE"
              },
              "debtor_person": {
                "salutation": "m",
                "first_name": "Fefas",
                "last_name": "Possum",
                "phone_number": "+49158888890",
                "email": "info@billie.io"
              },
              "line_items": [
                {
                  "external_id": "REF456",
                  "title": "iPhone 18",
                  "description": "",
                  "quantity": "2",
                  "amount": {
                    "gross": 3000,
                    "tax": 600,
                    "net": 2400
                  },
                  "category": "path",
                  "brand": "brand",
                  "gtin": "gtin",
                  "mpn": "mpn"
                }
              ]
            }
          }
        }
      }
      """

  Scenario: Authorize call is successful
    Given I have a checkout_session_id "142d6f47-cec2-4768-8de4-f7cbd1642bd1"
    And I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service register debtor positive response
    And Debtor has sufficient limit
    When I request "POST /authorizations" with body:
      """
      {
        "payment_method":
        {
          "payment_method_session_id": "142d6f47-cec2-4768-8de4-f7cbd1642bd1",
          "ui":
          {
            "data":
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
          }
        }
      }
      """
    And I save order uuid
    Then the response is 200 with body:
      """
      {
        "result": "accepted",
        "customer_order_reference": "{order_uuid}",
        "payment_method_reference": "{order_uuid}",
        "payment_method":
        {
          "ui":
          {
            "data":
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
            },
            "show": false,
            "uri": "uri"
          }
        }
      }
      """

  Scenario: Authorize call is rejected finally
    Given I have a checkout_session_id "142d6f47-cec2-4768-8de4-f7cbd1642bd1"
    And I get from companies service identify match response with similar candidate
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And I get from payments service register debtor positive response
    And Debtor has insufficient limit
    When I request "POST /authorizations" with body:
      """
      {
        "payment_method":
        {
          "payment_method_session_id": "142d6f47-cec2-4768-8de4-f7cbd1642bd1",
          "ui":
          {
            "data":
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
          }
        }
      }
      """
    Then the response is 200 with body:
      """
      {
        "result": "rejected",
        "payment_method":
        {
          "ui":
          {
            "data":
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
            },
            "show": false,
            "uri": "uri"
          }
        }
      }
      """


  Scenario: Authorize call is rejected with retry possible
    Given I have a checkout_session_id "142d6f47-cec2-4768-8de4-f7cbd1642bd1"
    Given I get from companies service identify no match response
    When I request "POST /authorizations" with body:
      """
      {
        "payment_method":
        {
          "payment_method_session_id": "142d6f47-cec2-4768-8de4-f7cbd1642bd1",
          "ui":
          {
            "data":
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
          }
        }
      }
      """
    Then the response is 200 with body:
      """
      {
        "result": "user_action_required",
        "payment_method":
        {
          "ui":
          {
            "data":
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
            },
            "show": false,
            "uri": "uri"
          }
        }
      }
      """
