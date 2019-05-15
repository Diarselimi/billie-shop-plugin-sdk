Feature:
  Depending on the merchant risk check settings, an order should be in waiting state if some risk checks failed.

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
      | limit                     |
      | debtor_not_customer       |
      | debtor_blacklisted        |
      | debtor_overdue            |
      | company_b2b_score         |
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
        "is_blacklisted": 0
      }
      """
    And I get from payments service get debtor response

  Scenario: Soft decline is enabled for limit check - all risk checks passed - order created successfully
    Given The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
    And I get from companies service identify match and good decision response
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
            "net":43,
            "gross":50,
            "tax":7
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
      }
      """
    Then the response status code should be 201
    And the order A1 is in state created

  Scenario: Soft decline is enabled for limit check - limit check failed - order in waiting state
    Given The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
    And I get from companies service identify match and good decision response
    And I get from companies service "/debtor/1/lock" endpoint response with status 412 and body
      """
      {}
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
            "net":1000.50,
            "gross":1001,
            "tax":0.50
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
      }
      """
	Then the response status code should be 201
	And the order A1 is in state waiting
  	And the order A1 has risk check limit failed

  Scenario: [order without external code] Soft decline is enabled for limit check - limit check failed - order in waiting state
    Given The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
    And I get from companies service identify match and good decision response
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
            "net":1000.50,
            "gross":1101,
            "tax":100.50
         },
         "comment":"Some comment",
         "duration":30
      }
      """
    Then the response status code should be 201
