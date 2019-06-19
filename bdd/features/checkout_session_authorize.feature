Feature: As a merchant, i should be able to create an order if I provide a valid session_id

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
      | limit                             |
      | debtor_not_customer               |
      | debtor_blacklisted                |
      | debtor_overdue                    |
      | company_b2b_score                 |
      | debtor_identified_strict          |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name                   |	enabled	|	decline_on_failure	|
      | available_financing_limit         |	1		|	0					|
      | amount                            |	1		| 	0					|
      | debtor_country                    |	1		| 	1					|
      | debtor_industry_sector            |	1		| 	1					|
      | debtor_identified                 |	1		| 	1					|
      | limit                             |	1		| 	0					|
      | debtor_not_customer               |	1		| 	1					|
      | debtor_blacklisted                |	1		| 	1					|
      | debtor_overdue                    |	1		| 	1					|
      | company_b2b_score                 |	1		| 	1					|
      | debtor_identified_strict          |	1		| 	1					|
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

  Scenario: I success if I try to create an order with a valid session_id
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "order_id":"A1"
    }
    """
    Then the order A1 is in state authorized
    And the response status code should be 201
    And the response should be empty

  Scenario: An order goes to declined if we cannot identify the company
    Given I get from companies service identify match and bad decision response
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "order_id":"A1"
    }
    """
    Then the order A1 is in state declined
    And the response status code should be 400
    And the JSON response at "reasons/0" should be "risk_policy"

  Scenario: An order goes to declined if it doesn't pass all the soft checks
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
          "net":333333.2,
          "gross":666643.30,
          "tax":333310.10
       },
       "comment":"Some comment",
       "duration":30,
       "dunning_status": null,
       "order_id":"A1"
    }
    """
    And the response status code should be 400
    Then the order A1 is in state declined

  Scenario:
    I success if I try to create an order with a valid session_id,
    but fail for the second time because the session_id should be invalidated!
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "order_id":"A1"
    }
    """
    Then the order A1 is in state authorized
    And the response status code should be 201
    And the response should be empty
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "order_id":"A1"
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"title":"Unauthorized","code":"unauthorized"}
    """

  Scenario: I success if I try to create an order with a valid session_id but gets declined,
  but fail for the second time because the session_id should be invalidated!
    Given I get from companies service identify match and bad decision response
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "order_id":"A1"
    }
    """
    Then the order A1 is in state declined
    And the response status code should be 400
    And the JSON response at "reasons/0" should be "risk_policy"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "order_id":"A1"
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"title":"Unauthorized","code":"unauthorized"}
    """

  Scenario: I fail authorization if I try to create an order with a invalid session_id
    Given I have a invalid checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
       "order_id":"A1"
    }
    """
    Then the JSON response should be:
    """
    {"title":"Unauthorized","code":"unauthorized"}
    """

  Scenario: Trying to create an order without an existing session id
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
    Then the JSON response should be:
    """
    {"title":"Unauthorized","code":"unauthorized"}
    """

  Scenario: Trying to create a order without amount data
    Given I get from companies service identify match and good decision response
    And I get from payments service register debtor positive response
    And I have a checkout_session_id "123123"
    And I send a PUT request to "/checkout-session/123123/authorize" with body:
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
          "established_customer":1
       },
       "delivery_address":{
          "house_number":"22",
          "street":"Charlot strasse",
          "city":"Paris",
          "postal_code":"98765",
          "country":"DE"
       },
       "comment":"Some comment",
       "duration":30,
       "dunning_status": null,
       "order_id":"A1"
    }
    """
    And the JSON response should be:
    """
    {"errors":[{"source":"amount.net","title":"This value should not be blank.","code":"request_validation_error"},{"source":"amount.gross","title":"This value should not be blank.","code":"request_validation_error"},{"source":"amount.tax","title":"This value should not be blank.","code":"request_validation_error"}]}
    """
    And the response status code should be 400
