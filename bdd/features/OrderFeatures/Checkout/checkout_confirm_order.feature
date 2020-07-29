Feature: As a merchant, I should be able to create an order by providing a valid session ID and data

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
      | debtor_identified_strict  |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
    And I get from companies service get debtor response
    And I get from payments service get debtor response

  Scenario: I successfully confirm the order by sending the same expected data. Order is moved to created state.
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And I get from companies service get debtor response
    And I get from payments service get order details response
    And Debtor lock limit call succeeded
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    # for debtor_company, use data from \PaellaCoreContext::iHaveADebtorWithoutOrders
    """
    {
       "amount":{
          "gross":100.0,
          "net":90.0,
          "tax":10.0
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
    Then the response status code should be 202
    And the order CO123 is in state created

  Scenario: I successfully confirm the order that is in pre_waiting state. Order is moved to waiting state.
    Given I have a pre_waiting order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And I get from companies service get debtor response
    And I get from payments service get order details response
    And Debtor lock limit call succeeded
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":100.0,
          "net":90.0,
          "tax":10.0
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
       }
    }
    """
    Then the response status code should be 202
    And the order CO123 is in state waiting

  Scenario: I fail to confirm the order if I do not send a request body
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I send a PUT request to "/checkout-session/123123CO123/confirm"
    Then the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.gross"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.net"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.tax"
        },
        {
          "title": "This value should be between 1 and 120.",
          "code": "request_validation_error",
          "source": "duration"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "debtor_company_request"
        }
      ]
    }
    """
    And the response status code should be 400

  Scenario: I fail to find the order by giving a wrong sessionUuid
    Given I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":100.0,
          "net":90.0,
          "tax":10.0
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
       }
    }
    """
    And the response status code should be 404

  Scenario: I fail to confirm the order if I send a different amount
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":101.0,
          "net":91.0,
          "tax":10.0
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
       }
    }
    """
    Then the order CO123 is in state authorized
    Then the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "Value of [amount] does not match the original one.",
          "code": "request_validation_error",
          "source": "amount",
          "source_value": {
            "gross":101.0,
            "net":91.0,
            "tax":10.0
         }
        }
      ]
    }
    """
    And the response status code should be 400

  Scenario: I fail to confirm the order if I send the wrong duration
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":100.0,
          "net":90.0,
          "tax":10.0
       },
       "duration":40,
       "debtor_company":{
          "name":"Test User Company",
          "legal_form": "GmbH",
          "address_addition":"lorem ipsum",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_city":"Berlin",
          "address_postal_code":"10179",
          "address_country":"DE"
       }
    }
    """
    Then the order CO123 is in state authorized
    Then the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "Value of [duration] does not match the original one.",
          "code": "request_validation_error",
          "source": "duration",
          "source_value": 40
        }
      ]
    }
    """
    And the response status code should be 400

  Scenario: I fail to confirm the order if I send mismatched debtor company
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a bad debtor strict match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":100.0,
          "net":90.0,
          "tax":10.0
       },
       "duration":30,
       "debtor_company":{
          "name":"Different Company",
          "address_addition":"lorem ipsum",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_city":"Somewhere",
          "address_postal_code":"10179",
          "address_country":"DE"
       }
    }
    """
    Then the order CO123 is in state authorized
    Then the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "Value of [debtor_company] does not match the original one.",
          "code": "request_validation_error",
          "source": "debtor_company",
          "source_value": {
              "name":"Different Company",
              "address_addition":"lorem ipsum",
              "address_house_number":"10",
              "address_street":"Heinrich-Heine-Platz",
              "address_city":"Somewhere",
              "address_postal_code":"10179",
              "address_country":"DE"
           }
        }
      ]
    }
    """
    And the response status code should be 400

  Scenario: Multiple mismatch errors returned when multiple properties have mismatches
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a bad debtor strict match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":101.0,
          "net":91.0,
          "tax":10.0
       },
       "duration":31,
       "debtor_company":{
          "name":"Different Company",
          "address_addition":"lorem ipsum",
          "address_house_number":"10",
          "address_street":"Heinrich-Heine-Platz",
          "address_city":"Somewhere",
          "address_postal_code":"10179",
          "address_country":"DE"
       },
       "delivery_address":{
          "addition":"lorem ipsum",
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Somewhere",
          "postal_code":"10179",
          "country":"DE"
       }
    }
    """
    Then the order CO123 is in state authorized
    Then the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "Value of [amount] does not match the original one.",
          "code": "request_validation_error",
          "source": "amount",
          "source_value": {
            "gross": 101,
            "net": 91,
            "tax": 10
          }
        },
        {
          "title": "Value of [duration] does not match the original one.",
          "code": "request_validation_error",
          "source": "duration",
          "source_value": 31
        },
        {
          "title": "Value of [delivery_address] does not match the original one.",
          "code": "request_validation_error",
          "source": "delivery_address",
          "source_value": {
            "addition": null,
            "house_number": "10",
            "street": "Heinrich-Heine-Platz",
            "city": "Somewhere",
            "postal_code": "10179",
            "country": "DE"
          }
        },
        {
          "title": "Value of [debtor_company] does not match the original one.",
          "code": "request_validation_error",
          "source": "debtor_company",
          "source_value": {
            "name": "Different Company",
            "address_addition": "lorem ipsum",
            "address_house_number": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_city": "Somewhere",
            "address_postal_code": "10179",
            "address_country": "DE"
          }
        }
      ]
    }
    """
    And the response status code should be 400

  Scenario: Successfully order confirmation returns exactly the same decimal numbers for amounts
    Given I have a authorized order "CO123" with amounts 100.3/99.1/1.2, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And I get from companies service get debtor response
    And I get from payments service get order details response
    And Debtor lock limit call succeeded
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":100.3,
          "net":99.1,
          "tax":1.2
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
       }
    }
    """
    Then the order CO123 is in state created
    And the response status code should be 202
    And the JSON at "amount" should be 100.3
    And the JSON at "amount_net" should be 99.1
    And the JSON at "amount_tax" should be 1.2

  Scenario: Returns proper errors when data is null
    Given I have a authorized order "CO123" with amounts 100.3/99.1/1.2, duration 30 and checkout session "123123CO123"
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount": null,
       "duration": null,
       "debtor_company": null,
       "delivery_address": null
    }
    """
    Then the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.gross"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.net"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.tax"
        },
        {
          "title": "This value should be between 1 and 120.",
          "code": "request_validation_error",
          "source": "duration"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "debtor_company_request"
        }
      ]
    }
    """
    And the response status code should be 400

  Scenario: Returns proper errors when data is not provided
    Given I have a authorized order "CO123" with amounts 100.3/99.1/1.2, duration 30 and checkout session "123123CO123"
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {}
    """
    Then the JSON response should be:
    """
    {
      "errors": [
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.gross"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.net"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "amount.tax"
        },
        {
          "title": "This value should be between 1 and 120.",
          "code": "request_validation_error",
          "source": "duration"
        },
        {
          "title": "This value should not be blank.",
          "code": "request_validation_error",
          "source": "debtor_company_request"
        }
      ]
    }
    """
    And the response status code should be 400

  Scenario: I fail to confirm the order if there is not enough limits
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And I get from companies service get debtor response
    And I get from payments service get order details response
    And Debtor lock limit call failed
    And I send a PUT request to "/checkout-session/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":100.0,
          "net":90.0,
          "tax":10.0
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
    Then the response status code should be 403
    And the order CO123 is in state authorized
