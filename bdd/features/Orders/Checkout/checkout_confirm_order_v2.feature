Feature: As a merchant, I should be able to create an order by providing a valid session ID and data

  # The whole feature replicates the checkout_confirm_order feature, only difference is request/response format
  # So it doesn't make sense to duplicate all the scenarios
  # What's left is only positive and two negative flows

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
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: I successfully confirm the order by sending the same expected data. Order is moved to created state.
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And Debtor lock limit call succeeded
    And I send a PUT request to "/public/api/v2/checkout-sessions/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":100.0,
          "net":90.0,
          "tax":10.0
       },
       "duration":30,
       "debtor":{
          "name":"Test User Company",
          "company_address": {
            "house_number":"10",
            "street":"Heinrich-Heine-Platz",
            "city":"Berlin",
            "postal_code":"10179",
            "country":"DE"
          }
       },
        "billing_address":{
          "addition":"lorem ipsum",
          "house_number":"10",
          "street":"Heinrich-Heine-Platz",
          "city":"Berlin",
          "postal_code":"10179",
          "country":"DE"
       },
       "external_code": "CO333"
    }
    """
    Then the response status code should be 202
    Then the json response should be:
    """
    {
       "external_code":"CO123",
       "uuid":"test-order-uuidCO123",
       "state":"created",
       "decline_reason":null,
       "amount":{
          "gross":100,
          "net":90,
          "tax":10
       },
       "unshipped_amount":{
          "gross":100,
          "net":90,
          "tax":10
       },
       "duration":30,
       "created_at":"2019-05-20 13:00:00",
       "delivery_address":{
          "street":"test",
          "house_number":"test",
          "postal_code":"test",
          "city":"test",
          "country":"TE"
       },
       "debtor":{
          "name":"Test User Company",
          "company_address":{
             "street":"Heinrich-Heine-Platz",
             "house_number":"10",
             "postal_code":"10179",
             "city":"Berlin",
             "country":"DE"
          },
          "billing_address":{
             "street":"test",
             "house_number":"test",
             "postal_code":"test",
             "city":"test",
             "country":"TE"
          },
          "bank_account":{
             "iban":"DE1234",
             "bic":"BICISHERE"
          },
          "external_data":{
             "merchant_customer_id":"ext_id",
             "name":"test",
             "industry_sector":"test",
             "address":{
                "street":"test",
                "house_number":"test",
                "postal_code":"test",
                "city":"testCity",
                "country":"TE"
             }
          }
       },
       "invoices":[
       ]
    }
    """
    And the order CO123 is in state created

  Scenario: I fail to confirm the order if I send a different amount
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a good debtor strict match response
    And I send a PUT request to "/checkout-sessions/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":101.0,
          "net":91.0,
          "tax":10.0
       },
       "duration":30,
       "debtor":{
          "name":"Test User Company",
          "legal_form": "GmbH",
          "company_address": {
            "addition":"lorem ipsum",
            "house_number":"10",
            "street":"Heinrich-Heine-Platz",
            "city":"Berlin",
            "postal_code":"10179",
            "country":"DE"
          }
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

  Scenario: Multiple mismatch errors returned when multiple properties have mismatches
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And I get from companies service a bad debtor strict match response
    And I send a PUT request to "/checkout-sessions/123123CO123/confirm" with body:
    """
    {
       "amount":{
          "gross":101.0,
          "net":91.0,
          "tax":10.0
       },
       "duration":31,
       "debtor":{
          "name":"Different Company",
          "company_address": {
            "addition":"lorem ipsum",
            "house_number":"10",
            "street":"Heinrich-Heine-Platz",
            "city":"Berlin",
            "postal_code":"10179",
            "country":"DE"
          }
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
                "source_value": {
                    "gross": 101,
                    "net": 91,
                    "tax": 10
                },
                "source": "amount",
                "title": "Value of [amount] does not match the original one.",
                "code": "request_validation_error"
            },
            {
                "source_value": {
                    "house_number": "10",
                    "street": "Heinrich-Heine-Platz",
                    "postal_code": "10179",
                    "country": "DE",
                    "addition": null,
                    "city": "Somewhere"
                },
                "source": "delivery_address",
                "title": "Value of [delivery_address] does not match the original one.",
                "code": "request_validation_error"
            },
            {
                "source_value": {
                    "name": "Different Company",
                    "company_address": {
                        "house_number": "10",
                        "street": "Heinrich-Heine-Platz",
                        "postal_code": "10179",
                        "country": "DE",
                        "addition": null,
                        "city": "Berlin"
                    }
                },
                "source": "debtor_company",
                "title": "Value of [debtor_company] does not match the original one.",
                "code": "request_validation_error"
            }
        ]
    }
    """
    And the response status code should be 400
