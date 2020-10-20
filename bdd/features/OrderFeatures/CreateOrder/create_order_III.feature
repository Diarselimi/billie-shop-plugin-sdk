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
      | debtor_overdue            |
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
      | debtor_overdue            | 1       | 1                  |
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
  Scenario: Successful order creation when external code (order_id) is not passed in request
    Given I get from companies service identify match response
    And I get from scoring service good debtor scoring decision for debtor "c7be46c0-e049-4312-b274-258ec5aeeb70"
    And Debtor has sufficient limit
    And Debtor lock limit call succeeded
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
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "amount":{
          "net":900.00,
          "gross":1000.00,
          "tax":100.00
       },
       "comment":"Some comment",
       "duration":30
    }
    """
    And the response status code should be 200
    And the JSON response should be:
    """
    {
       "order_id":null,
       "state":"created",
       "reasons":null,
       "decline_reason":null,
       "amount":1000,
       "amount_net":900,
       "amount_tax":100,
       "duration":30,
       "dunning_status":null,
       "debtor_company":{
          "name":"Test User Company",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_postal_code":"10179",
          "address_city":"Berlin",
          "address_country":"DE"
       },
       "bank_account":{
          "iban":"DE1234",
          "bic":"BICISHERE"
       },
       "invoice":{
          "invoice_number":null,
          "payout_amount":null,
          "outstanding_amount":null,
          "fee_amount":null,
          "fee_rate":null,
          "due_date":null,
          "pending_merchant_payment_amount":null,
          "pending_cancellation_amount":null
       },
       "debtor_external_data":{
          "merchant_customer_id":"12",
          "name":"Test User Company",
          "address_country":"DE",
          "address_city":"Berlin",
          "address_postal_code":"10179",
          "address_street":"Heinrich-Heine-Platz",
          "address_house":"10",
          "industry_sector":"SOME SECTOR"
       },
       "delivery_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "billing_address":{
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "created_at":"2020-02-18T12:28:46+0100",
       "shipped_at":null,
       "debtor_uuid":null
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
