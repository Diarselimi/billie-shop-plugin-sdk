Feature:
  If I use invalid amount values, I should get a proper 400 error

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
      | delivery_address                  |
      | debtor_is_trusted                 |
      | limit                             |
      | debtor_not_customer               |
      | debtor_blacklisted                |
      | debtor_overdue                    |
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
      | delivery_address                  | 1       | 1                  |
      | debtor_identified_strict          | 1       | 1                  |
      | debtor_is_trusted                 | 1       | 1                  |
      | limit                             | 1       | 1                  |
      | debtor_not_customer               | 1       | 1                  |
      | debtor_blacklisted                | 1       | 1                  |
      | debtor_overdue                    | 1       | 1                  |
      | company_b2b_score                 | 1       | 1                  |
    And I get from companies service get debtor response
    And I get from payments service get debtor response

  Scenario: Fail when amount values are invalid floats on create order
    Given I have a complete order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order" with body:
    """
    {
       "amount":{
          "net":"S900.00",
          "gross":"000.00",
          "tax":null
       },
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
       "comment":"Some comment",
       "duration":30,
       "order_id":"A1"
    }
    """
    Then the response status code should be 400

  Scenario: Fail when amount values are invalid floats on confirm checkout order
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "net":null,
          "gross":010.00,
          "tax":"abc"
       },
       "duration":30,
       "debtor_company":{
          "name":"Test User Company",
          "legal_form": "GmbH",
          "address_addition":"lorem ipsum",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_city":"Berlin",
          "address_postal_code":"10179",
          "address_country":"DE"
       },
        "billing_address":{
          "addition":"lorem ipsum",
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       }
    }
    """
    Then the response status code should be 400
    And the order CO123 is in state authorized
