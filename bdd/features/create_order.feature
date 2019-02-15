Feature:
  In order to create an order
  I send the order data to the endpoint
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1

  Scenario: Debtor identification failed
    Given I get from alfred "/debtor/identify" endpoint response with status 404 and body
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
    Then the order A1 is declined
    And the response should be empty

  Scenario: Successful order creation
    Given I get from alfred "/debtor/identify" endpoint response with status 200 and body
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
    And I get from alfred "/debtor/1/is-eligible-for-pay-after-delivery" endpoint response with status 200 and body
      """
      {
        "is_eligible": true
      }
      """
    And I get from borscht "/debtor.json" endpoint response with status 200 and body
      """
      {
        "debtor_id": 1
      }
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
    And the response should be empty

  Scenario: Debtor overdue check failed
    Given I get from alfred "/debtor/identify" endpoint response with status 200 and body
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
    And I get from alfred "/debtor/1/is-eligible-for-pay-after-delivery" endpoint response with status 200 and body
      """
      {
        "is_eligible": true
      }
      """
    And I get from borscht "/debtor.json" endpoint response with status 200 and body
      """
      {
        "debtor_id": 2
      }
      """
    And I have a late order XLO123 with amounts 1002/901/101, duration 30 and comment "test order"
    And Order XLO123 was shipped at "2018-01-01 00:00:00"
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
    And the response should be empty
    Then the order A1 is declined

  Scenario: Debtor is not eligible for Point Of Sale
    Given I get from alfred "/debtor/identify" endpoint response with status 200 and body
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
    And I get from alfred "/debtor/1/is-eligible-for-pay-after-delivery" endpoint response with status 200 and body
      """
      {
        "passed": is_eligible
      }
      """
    And I get from borscht "/debtor.json" endpoint response with status 200 and body
      """
      {
        "debtor_id": 1
      }
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
    And the response should be empty
    And the order A1 is declined

  Scenario: Missing required fields
    When I send a POST request to "/order" with body:
      """
        {
           "debtor_person":{
              "salutation":"m",
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
	Given I get from alfred "/debtor/identify" endpoint response with status 200 and body
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
	And I get from alfred "/debtor/1/is-eligible-for-pay-after-delivery" endpoint response with status 200 and body
      """
      {
        "is_eligible": true
      }
      """
	And I get from borscht "/debtor.json" endpoint response with status 200 and body
      """
      {
        "debtor_id": 1
      }
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
	And the response should be empty

  Scenario: Order exceeds the merchant available financing limit
    Given I get from alfred "/debtor/identify" endpoint response with status 200 and body
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
    Then the order A1 is declined
    And the response should be empty
