Feature:
  In order to retrieve the order details
  I want to call the get order endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1

    Scenario: Unsuccessful order retrieve - order doesn't exist
      When I send a GET request to "/order/ABC"
      Then the response status code should be 404
      And the JSON response should be:
      """
      {
          "code": "not_found",
          "error": "Order #ABC not found"
      }
      """

    Scenario: Successful order retrieval
      Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
      And I get from companies service identify match and good decision response
      And I get from payments service get debtor response
      When I send a GET request to "/order/XF43Y"
      Then the response status code should be 200
      And the JSON response should be:
      """
      {
          "external_code": "XF43Y",
          "state": "new",
          "reasons": [],
          "amount": 1000,
          "debtor_company": {
              "name": "Test User Company",
              "house_number": "10",
              "street": "Heinrich-Heine-Platz",
              "postal_code": "10179",
              "city": "Berlin",
              "country": "DE"
          },
          "bank_account": {
              "iban": "DE1234",
              "bic": "BICISHERE"
          },
          "invoice": {
              "number": null,
              "payout_amount": null,
              "fee_amount": null,
              "fee_rate": null,
              "due_date": null
          },
          "debtor_external_data": {
              "name": "test",
              "address_country": "TE",
              "address_postal_code": "test",
              "address_street": "test",
              "address_house": "test",
              "industry_sector": "test"
          }
      }
      """

  Scenario: Successful declined order retrieval
    Given I have a declined order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service identify match and good decision response
    And I get from companies service get debtor response
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
      """
      {
          "external_code": "XF43Y",
          "state": "declined",
          "reasons": ["risk_policy"],
          "amount": 1000,
          "debtor_company": {
              "name": "Test User Company",
              "house_number": "10",
              "street": "Heinrich-Heine-Platz",
              "postal_code": "10179",
              "city": "Berlin",
              "country": "DE"
          },
           "bank_account": {
              "iban": null,
              "bic": null
          },
          "invoice": {
              "number": null,
              "payout_amount": null,
              "fee_amount": null,
              "fee_rate": null,
              "due_date": null
          },
          "debtor_external_data": {
              "name": "test",
              "address_country": "TE",
              "address_postal_code": "test",
              "address_street": "test",
              "address_house": "test",
              "industry_sector": "test"
          }
      }
      """
