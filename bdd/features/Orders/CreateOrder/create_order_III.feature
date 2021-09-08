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
      | delivery_address          |
      | debtor_is_trusted         |
      | limit                     |
      | debtor_not_customer       |
      | debtor_blacklisted        |
      | company_b2b_score         |
      | line_items                |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | line_items                | 1       | 1                  |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | delivery_address          | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | debtor_is_trusted         | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |

  Scenario: Invalid money values
    When I send a POST request to "/order" with body:
      """
        {
           "debtor_person":{
              "salutation":"m",
              "first_name":"John",
              "last_name":"Smith",
              "phone_number":"+491234567",
              "email":"foo@bar.com"
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
              "net":"not a number",
              "gross":1000.123456,
              "tax": -1
           },
           "comment":"Some comment",
           "duration":60,
           "order_id":"CO123"
        }
      """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
       "errors":[
        {
          "title": "Invalid values: gross is not equal to net + tax.",
          "code": "request_validation_error",
          "source": "amount"
        },
        {
          "title": "This value should not have more than 2 decimals.",
          "code": "request_validation_error",
          "source": "amount.gross"
        },
        {
          "title": "This value should be numeric.",
          "code": "request_validation_error",
          "source": "amount.net"
        },
        {
          "title": "This value should be greater than or equal to 0.",
          "code": "request_validation_error",
          "source": "amount.tax"
        }
       ]
      }
    """

  Scenario: Invalid external code (order_id)
    When I send a POST request to "/order" with body:
      """
        {
           "debtor_person":{
              "salutation":"m",
              "first_name":"John",
              "last_name":"Smith",
              "phone_number":"+491234567",
              "email":"foo@bar.com"
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
              "net":900,
              "gross":1000,
              "tax":100
           },
           "comment":"Some comment",
           "duration":60,
           "order_id":12
        }
      """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
       "errors":[
        {
          "title": "This value should be of type string.",
          "code": "request_validation_error",
          "source": "external_code"
        }
       ]
      }
    """

  Scenario: Blank order id
    When I send a POST request to "/order" with body:
      """
        {
           "debtor_person":{
              "salutation":"m",
              "first_name":"John",
              "last_name":"Smith",
              "phone_number":"+491234567",
              "email":"foo@bar.com"
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
              "net": 50,
              "gross":60,
              "tax": 10
           },
           "comment":"Some comment",
           "duration":60,
           "order_id":""
        }
      """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
       "errors":[
        {
          "title": "This value should be null or non-blank string.",
          "code": "request_validation_error",
          "source": "external_code"
        }
       ]
      }
    """
