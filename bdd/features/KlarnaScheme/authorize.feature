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
        "result": "user_action_required",
        "payment_method":
        {
          "ui":
          {
            "data":
            {
              "amount":
              {
                "gross": 5,
                "net": 4,
                "tax": 1
              },
              "duration": 30
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
    Then the response is 200 with body:
      """
      {
        "result": "accepted",
        "customer_order_reference": "uuid",
        "payment_method_reference": "uuid",
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
