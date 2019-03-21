Feature:
  In order to create an order
  I send the order data to the endpoint
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And The following risk check definitions exist:
      | name                              |
      | available_financing_limit         |
      | amount                            |
      | debtor_country                    |
      | debtor_industry_sector            |
      | debtor_identified                 |
      | limit                             |
      | debtor_not_customer               |
      | debtor_name                       |
      | debtor_address_street_match       |
      | debtor_address_house_match        |
      | debtor_address_postal_code_match  |
      | debtor_blacklisted                |
      | debtor_overdue                    |
      | company_b2b_score                 |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name                   |	enabled	|	decline_on_failure	|
      | available_financing_limit         |	1		|	1					|
      | amount                            |	1		| 	1					|
      | debtor_country                    |	1		| 	1					|
      | debtor_industry_sector            |	1		| 	1					|
      | debtor_identified                 |	1		| 	1					|
      | limit                             |	1		| 	1					|
      | debtor_not_customer               |	1		| 	1					|
      | debtor_name                       |	1		| 	1					|
      | debtor_address_street_match       |	1		| 	1					|
      | debtor_address_house_match        |	1		| 	1					|
      | debtor_address_postal_code_match  |	1		| 	1					|
      | debtor_blacklisted                |	1		| 	1					|
      | debtor_overdue                    |	1		| 	1					|
      | company_b2b_score                 |	1		| 	1					|


  Scenario: Debtor identification failed
    Given I get from companies service identify no match response
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
    And the JSON response should be:
    """
    {}
    """

  Scenario: Successful order creation
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
    Then the response status code should be 201
    And the JSON response should be:
    """
    {}
    """
    And the order A1 is in state created

  Scenario: Successful order creation using lowercase country
    Given I get from companies service identify match and good decision response
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
            "net":33.2,
            "gross":43.30,
            "tax":10.10
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
    }
    """
    Then the response status code should be 201
    And the JSON response should be:
    """
    {}
    """
    And the order A1 is in state created

  Scenario: Debtor overdue check failed
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I have a late order XLO123 with amounts 1002/901/101, duration 30 and comment "test order"
    And Order XLO123 was shipped at "2018-01-01 00:00:00"
    When I send a POST request to "/order" with body:
    """
    {
         "debtor_person":{
            "salutation":"f",
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
            "net":33.2,
            "gross":43.30,
            "tax":10.10
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
    }
    """
    Then the response status code should be 201
    And the JSON response should be:
    """
    {}
    """
    Then the order A1 is in state declined

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
            "net":33.2,
            "gross":43.30,
            "tax":10.10
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
    }
    """
    Then the response status code should be 201
    And the JSON response should be:
    """
    {}
    """
    And the order A1 is in state declined

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
               "title":"This value should not be blank.",
               "code":"request_validation_error"
            },
            {
               "source":"external_code",
               "title":"This value should not be blank.",
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
              "net":33.2,
              "gross":43.30,
              "tax":10.10
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
              "net":33.2,
              "gross":43.30,
              "tax":10.10
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
            "source":"delivery_address.postal_code",
            "title":"This value is not valid.",
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
            "net":33.2,
            "gross":43.30,
            "tax":10.10
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
      }
      """
    Then the response status code should be 201
    And the JSON response should be:
    """
    {}
    """
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
            "net":30000.00,
            "gross": 30000.00,
            "tax":0
         },
         "comment":"Some comment",
         "duration":30,
         "order_id":"A1"
      }
      """
    Then the response status code should be 201
    And the JSON response should be:
    """
    {}
    """
    And the order A1 is in state declined
