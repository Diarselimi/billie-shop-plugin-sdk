Feature:
  I need to create an order in state pre_approved so that I can send the response to the endpoint
  about the order if it's created or not and if the order is not accepted in 30 days than it will automatically get declined

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
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
    And I get from companies service get debtor response
    And I get from payments service get debtor response
    And I get from payments service register debtor positive response


  Scenario: Successfully create an order in pre-approved state
    Given I get from companies service identify match and good decision response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I send a POST request to "/order/pre-approve" with body:
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
          "established_customer":1,
          "merchant_customer_id":"1"
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
       "order_id":"A1"
    }
    """
    Then the order A1 is in state pre_approved
    And the response status code should be 200
    And the response should contain "pre_approved"
    And merchant debtor has financing power 10000

  Scenario: Successfully create an order in pre-approved state without house
    Given I get from companies service identify match and good decision response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I send a POST request to "/order/pre-approve" with body:
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
          "established_customer":1,
          "merchant_customer_id":"1"
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
       "order_id":"A1"
    }
    """
    Then the order A1 is in state pre_approved
    And the response status code should be 200
    And the response should contain "pre_approved"
    And merchant debtor has financing power 10000

  Scenario: Debtor identification failed
    Given I get from companies service identify match and bad decision response
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
    And I send a POST request to "/order/pre-approve" with body:
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
          "established_customer":1,
          "merchant_customer_id":"1"
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
       "order_id":"A1"
    }
    """
    Then the order A1 is in state declined
    And the response status code should be 200
    And the response should contain "declined"
    And merchant debtor has financing power 10000

  Scenario: Order success confirmation when the order exists
    Given I have a pre_approved order "CO123" with amounts 55.2/43.30/10.10, duration 30 and comment "test order"
    And I get from companies service identify match and good decision response
    And I get from companies service get debtor response
    And I send a POST request to "/order/test-order-uuid/confirm" with body:
    """
    """
    Then the response status code should be 200
    And the order CO123 is in state created
    And merchant debtor has financing power 944.8

  Scenario: Order success confirmation when the order does not exists
    Given I have a pre_approved order "CO123" with amounts 55.2/43.30/10.10, duration 30 and comment "test order"
    And I get from companies service identify match and good decision response
    And I send a POST request to "/order/not_existent_uuid/confirm" with body:
    """
    """
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
    """
    And merchant debtor has financing power 1000

  Scenario: Order success confirmation when the order exists in another state than pre_confirmed
    Given I have a created order "CO123" with amounts 55.2/43.30/10.10, duration 30 and comment "test order"
    And I get from companies service identify match and good decision response
    And I send a POST request to "/order/test-order-uuid/confirm" with body:
    """
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {"errors":[{"title":"The order is not in pre approved state to be confirmed","code":"request_invalid"}]}
    """
    And merchant debtor has financing power 1000
