Feature:
  If the order creation fails with multiple reasons then the one that was declined with should be returned as a reason.

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
      | debtor_blacklisted        |
      | debtor_overdue            |
      | company_b2b_score         |
      | debtor_is_trusted         |
      | debtor_identified_strict  |
      | delivery_address          |
    And I get from companies service get debtor response
    And I get from payments service get debtor response

  Scenario: When multiple reasons fail then we should return the reason that was declined for.
    Given The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 0                  |
      | amount                    | 1       | 0                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | delivery_address          | 1       | 0                  |
      | debtor_identified_strict  | 1       | 1                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
    And I get from companies service identify match response
    And I get from scoring service bad debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has insufficient limit
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
          "merchant_customer_id":"1",
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
          "net":9133.2,
          "gross":9143.30,
          "tax":10.10
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the order A1 is in state declined
    And the response status code should be 200
    And the JSON at "decline_reason" should be "debtor_limit_exceeded"

  Scenario: When multiple reasons fail then we should return the reason that was declined for.
    Given The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | delivery_address          | 1       | 0                  |
      | debtor_identified_strict  | 1       | 1                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
    And I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has insufficient limit
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
          "merchant_customer_id":"1",
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
          "net":9133.2,
          "gross":9143.30,
          "tax":10.10
       },
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the order A1 is in state declined
    And the JSON at "decline_reason" should be "debtor_limit_exceeded"
    And the response status code should be 200

  Scenario: The right reason should be returned after multiple reasons fail.
    Given The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 0                  |
      | delivery_address          | 1       | 0                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
    And I get from companies service identify no match and respond with suggestion
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has insufficient limit
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
          "merchant_customer_id":"1",
          "name":"Test User Company",
          "address_house_number":"5",
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
          "legal_form":"99999",
          "established_customer":1
       },
       "delivery_address":{
          "house_number":"29",
          "street":"different str.",
          "city":"Stuttgart",
          "postal_code":"80469",
          "country":"DE"
       },
       "amount":{
          "net": 9000.00,
          "gross":9000.00,
          "tax": 0.00
       },
       "comment":"Some comment",
       "duration":15,
       "order_id":"A1"
    }
    """
    Then the order A1 is in state waiting
    And the JSON at "decline_reason" should be "debtor_address"
    And the response status code should be 200

  Scenario: Successfully decline order in pre_approved state
    Given I have a pre_approved order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And The following notification settings exist for merchant 1:
        | notification_type | enabled |
        | order_declined    | 1       |
    When I send a POST request to "/private/order/test-order-uuidCO123/decline"
    Then the response status code should be 204
    And the order CO123 is in state declined
    And Order notification should NOT exist for order "CO123" with type "order_declined"
